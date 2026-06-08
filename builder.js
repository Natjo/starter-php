const fs = require('fs-extra');
const path = require('path');
const crypto = require('crypto');
const postcss = require('postcss');
const cssCustomMedia = require('postcss-custom-media');
const postcssGlobalData = require('@csstools/postcss-global-data');
const parser = require('postcss-comment');
const postcssExtendRule = require('postcss-extend-rule');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');
const babel = require('@babel/core');
const esbuild = require('esbuild');
const chokidar = require('chokidar');

const root = __dirname;
const src = path.join(root, 'assets');
const webAssets = path.join(root, 'web', 'assets');
const fromCss = 'app.css';
const legacyFromCss = 'styles.css';
const criticalCss = 'critical.css';
const cssBundles = {
    common: 'common.css',
};
const cssImportTargetNames = new Set(['critical', ...Object.keys(cssBundles)]);
const cssManifestFile = 'css-bundles.json';
const buildManifestFile = 'build.json';
const versionedAssetExtensions = new Set([
    '.css',
    '.js',
    '.avif',
    '.gif',
    '.ico',
    '.jpeg',
    '.jpg',
    '.png',
    '.svg',
    '.webp',
    '.woff',
    '.woff2',
]);
const bundledCssSourceDirs = new Set(['cards', 'common', 'components', 'modules', 'strates', 'vendors']);
const templateSourceDirs = new Set(['cards', 'common', 'components', 'form', 'heros', 'strates']);
const excludedCopyDirs = new Set([...templateSourceDirs, 'modules', 'pages', 'styles']);
const importAliases = {
    'common': path.join(src, 'common'),
    '@common': path.join(src, 'common'),
    '@components': path.join(src, 'components'),
    '@modules': path.join(src, 'modules'),
    '@vendors': path.join(src, 'vendors'),
};
let appCssImports = new Set();
let criticalCssImports = new Set();
let bundledCssImports = new Set();
let cssImportTargets = new Map();
let cssManifest = {
    bundles: {},
    bundledFiles: [],
    fileBundles: {},
};
const rebuildTimers = new Map();
const rebuildSignatures = new Map();
let fullBuildTimer = null;
let sourceSnapshot = new Map();
let currentSourceFiles = null;
const shouldWatch = process.argv.includes('--watch');
const isProd = process.argv.includes('--prod');
let displayBuildStatus = !shouldWatch && !isProd;

const postcssPlugins = [
    postcssExtendRule,
    postcssGlobalData({ files: [path.join(src, 'styles/customMedias.css')] }),
    cssCustomMedia(),
    autoprefixer({ add: true }),
];

if (isProd) {
    postcssPlugins.unshift(cssnano);
}

const processor = postcss(postcssPlugins);

const toPosix = file => file.split(path.sep).join('/');

const isIgnored = file => path.basename(file) === '.DS_Store' || (isProd && path.extname(file) === '.map');

const sourceAppCss = () => {
    const appCss = path.join(src, fromCss);
    if (fs.existsSync(appCss)) return appCss;
    return path.join(src, legacyFromCss);
};

const isAppCss = file => {
    const source = sourceAppCss();
    return path.resolve(file) === path.resolve(source);
};

const isAppJs = file => path.resolve(file) === path.join(src, 'app.js');

const topLevelDir = file => {
    const relative = toPosix(path.relative(src, file));
    return relative.split('/')[0];
};

const isCopiedDirFile = file => {
    const dir = topLevelDir(file);
    return Boolean(dir) && !dir.startsWith('..') && !excludedCopyDirs.has(dir) && fs.existsSync(path.join(src, dir)) && fs.statSync(path.join(src, dir)).isDirectory();
};

const isVendorFile = file => topLevelDir(file) === 'vendors';
const isStylesSourceFile = file => topLevelDir(file) === 'styles';
const isTemplateFile = file => templateSourceDirs.has(topLevelDir(file)) && path.extname(file) === '.php';
const isJsFile = file => path.extname(file) === '.js';
const isCssFile = file => path.extname(file) === '.css';

const outputPath = file => path.join(webAssets, path.relative(src, file));

const autoBundleGroupForRelativeCss = file => {
    return null;
};

const bundleGroupForRelativeCss = file => {
    const explicitTarget = cssImportTargets.get(file);

    if (explicitTarget === 'critical') return null;
    if (cssBundles[explicitTarget]) return explicitTarget;

    return autoBundleGroupForRelativeCss(file);
};

const resolveAliasImport = specifier => {
    const alias = Object.keys(importAliases).find(name => specifier === name || specifier.startsWith(`${name}/`));

    if (!alias) return null;

    const base = importAliases[alias];
    const relative = specifier === alias ? '' : specifier.slice(alias.length + 1);
    const target = path.join(base, relative);
    const candidates = [
        target,
        `${target}.mjs`,
        `${target}.js`,
        path.join(target, 'index.mjs'),
        path.join(target, 'index.js'),
        path.join(target, `${path.basename(target)}.mjs`),
        path.join(target, `${path.basename(target)}.js`),
    ];

    return candidates.find(candidate => fs.existsSync(candidate) && fs.statSync(candidate).isFile()) || null;
};

const relativeImport = (from, to) => {
    const relative = toPosix(path.relative(path.dirname(from), to));
    return relative.startsWith('.') ? relative : `./${relative}`;
};

const fileHash = file => crypto
    .createHash('md5')
    .update(fs.readFileSync(file))
    .digest('hex')
    .slice(0, 10);

const stripQuery = specifier => specifier.split('?')[0].split('#')[0];

const resolveJsImport = (from, specifier) => resolveAliasImport(specifier) || resolveRelativeJsImport(from, specifier);

const resolveRelativeJsImport = (from, specifier) => {
    if (!specifier.startsWith('.')) return null;

    const cleanSpecifier = stripQuery(specifier);
    const target = path.resolve(path.dirname(from), cleanSpecifier);
    const candidates = [
        target,
        `${target}.mjs`,
        `${target}.js`,
        path.join(target, 'index.mjs'),
        path.join(target, 'index.js'),
        path.join(target, `${path.basename(target)}.mjs`),
        path.join(target, `${path.basename(target)}.js`),
    ];

    return candidates.find(candidate => fs.existsSync(candidate) && fs.statSync(candidate).isFile()) || null;
};

const jsVersionHash = file => {
    const compiled = outputPath(file);
    const versionedFile = fs.existsSync(compiled) ? compiled : file;

    return fileHash(versionedFile);
};

const jsImportPath = (from, to) => {
    const relative = relativeImport(from, to);

    return isProd ? `${relative}?v=${jsVersionHash(to)}` : relative;
};

const babelAliasPlugin = () => ({
    visitor: {
        'ImportDeclaration|ExportNamedDeclaration|ExportAllDeclaration'(babelPath, state) {
            const source = babelPath.node.source;

            if (!source) return;

            const resolved = resolveJsImport(state.file.opts.filename, source.value);

            if (resolved) {
                source.value = jsImportPath(state.file.opts.filename, resolved);
            }
        },
    },
});

const appBundlePlugin = {
    name: 'app-bundle',
    setup(build) {
        build.onResolve({ filter: /^(common|@[^/]+)(\/.*)?$/ }, args => {
            const resolved = resolveAliasImport(args.path);
            if (!resolved) return null;

            return { path: resolved };
        });

        build.onResolve({ filter: /^\.\/(common|components|form|modules|strates)\// }, args => ({
            path: args.path,
            external: true,
        }));
    },
};

const display = (file, status) => {
    if (!displayBuildStatus && status !== 'error') return;

    const colors = {
        add: '36m',
        remove: '31m',
        update: '32m',
        error: '31m',
    };
    console.log(`\x1b[1m${displayFileLabel(file)}\x1b[22m`, `\x1b[${colors[status] || '90m'}${status}\x1b[39m`);
};

const cssTargetLabel = target => {
    const colors = {
        critical: '35m',
        common: '36m',
        components: '33m',
        modules: '34m',
    };

    return `\x1b[${colors[target] || '90m'}${target}\x1b[39m`;
};

const displayFileLabel = file => {
    const parts = String(file).split('/');
    const scopedRoots = new Set(['cards', 'components', 'heros', 'strates']);

    if (parts.length > 1 && scopedRoots.has(parts[0])) {
        return `${parts[0]} - ${parts[parts.length - 1]}`;
    }

    return file;
};

async function runPostcss(css, from, to) {
    const result = await processor.process(css, {
        from,
        to,
        parser,
        map: isProd ? false : { inline: false, annotation: `${path.basename(to)}.map` },
    });

    fs.ensureDirSync(path.dirname(to));
    fs.writeFileSync(to, result.css);

    if (result.map) {
        fs.writeFileSync(`${to}.map`, result.map.toString());
    }
}

const rewriteCssUrls = (css = '', fromFile, toFile) => {
    const sourceDir = path.dirname(fromFile);
    const outputDir = path.dirname(toFile);

    return css.replace(/url\((["']?)([^"')]+)\1\)/g, (match, quote, url) => {
        const trimmed = url.trim();

        if (/^(?:[a-z]+:|\/\/|#|data:)/i.test(trimmed)) {
            return match;
        }

        const sourceRelative = path.resolve(sourceDir, trimmed);
        const rootRelative = path.resolve(src, trimmed);
        const absolute = fs.existsSync(sourceRelative) ? sourceRelative : rootRelative;
        const asset = absolute.startsWith(src) ? path.join(webAssets, path.relative(src, absolute)) : absolute;
        const relative = toPosix(path.relative(outputDir, asset));
        const rewritten = relative.startsWith('.') ? relative : `./${relative}`;

        return `url(${quote}${rewritten}${quote})`;
    });
};

const cssImportPattern = /^\s*@import\s+["']([^"']+)["']\s*([a-z-]+)?\s*;?/igm;

function parseCssImports(css = '') {
    const imports = [];

    css.replace(cssImportPattern, (match, importedFile, target) => {
        const normalized = toPosix(path.normalize(importedFile));
        const normalizedTarget = target ? target.toLowerCase() : null;

        if (normalizedTarget && !cssImportTargetNames.has(normalizedTarget)) {
            throw new Error(`Target CSS invalide "${target}" pour l'import "${importedFile}". Valeurs autorisees : ${[...cssImportTargetNames].join(', ')}`);
        }

        imports.push({
            file: normalized,
            target: normalizedTarget,
        });
    });

    return imports;
}

const isDefaultCriticalCssImport = file => file.startsWith('styles/') && file !== 'styles/print.css';

const cssImportRelatives = importedFile => resolveCssImport(importedFile).map(file => toPosix(path.relative(src, file)));

function collectCssImportTargets(imports) {
    const criticalFiles = new Set();
    const bundledFiles = new Set();
    const explicitTargets = new Map();

    imports.forEach(({ file, target }) => {
        const files = cssImportRelatives(file);

        files.forEach(relative => {
            if (target) {
                explicitTargets.set(relative, target);

                if (target === 'critical') {
                    criticalFiles.add(relative);
                } else {
                    bundledFiles.add(relative);
                }

                return;
            }

            if (isDefaultCriticalCssImport(relative)) {
                criticalFiles.add(relative);
                return;
            }

            if (autoBundleGroupForRelativeCss(relative)) {
                bundledFiles.add(relative);
            }
        });
    });

    return {
        criticalFiles,
        bundledFiles,
        explicitTargets,
    };
}

function inlineImports(css = '') {
    return css.replace(cssImportPattern, (match, importedFile, target) => {
        const normalized = toPosix(path.normalize(importedFile));
        const normalizedTarget = target ? target.toLowerCase() : null;

        if (normalizedTarget && !cssImportTargetNames.has(normalizedTarget)) {
            throw new Error(`Target CSS invalide "${target}" pour l'import "${importedFile}". Valeurs autorisees : ${[...cssImportTargetNames].join(', ')}`);
        }

        if (normalizedTarget !== 'critical' && (normalizedTarget || !isDefaultCriticalCssImport(normalized))) {
            return '';
        }

        const out = path.join(webAssets, criticalCss);
        return resolveCssImport(normalized)
            .map(importedPath => rewriteCssUrls(fs.readFileSync(importedPath, 'utf8'), importedPath, out))
            .join('\n') + '\n';
    });
}

function resolveCssImport(importedFile) {
    const normalized = toPosix(path.normalize(importedFile));
    const target = path.resolve(src, normalized.replace(/\/\*{1,2}$/, ''));
    const candidates = [
        target,
        `${target}.css`,
        path.join(target, `${path.basename(target)}.css`),
        path.join(target, 'index.css'),
    ];
    const file = candidates.find(candidate => fs.existsSync(candidate) && fs.statSync(candidate).isFile());

    if (file) return [file];

    if (fs.existsSync(target) && fs.statSync(target).isDirectory()) {
        return getFiles(target).filter(candidate => isCssFile(candidate)).sort();
    }

    throw new Error(`Import CSS introuvable : ${importedFile}`);
}

function writeCssManifest() {
    cssManifest.bundledFiles = [...bundledCssImports].sort();
    cssManifest.fileBundles = {};

    cssManifest.bundledFiles.forEach(file => {
        const group = bundleGroupForRelativeCss(file);

        if (group) {
            cssManifest.fileBundles[file] = group;
        }
    });

    fs.ensureDirSync(webAssets);
    fs.writeJsonSync(path.join(webAssets, cssManifestFile), cssManifest, { spaces: 2 });
}

function refreshAppCssImports() {
    const file = sourceAppCss();

    if (!fs.existsSync(file)) {
        display(path.relative(root, file), 'error');
        throw new Error(`Fichier CSS principal introuvable : ${path.relative(root, file)}`);
    }

    const source = fs.readFileSync(file, 'utf8');
    const imports = parseCssImports(source);
    const importTargets = collectCssImportTargets(imports);

    appCssImports = new Set(imports.map(imported => imported.file));
    cssImportTargets = importTargets.explicitTargets;
    criticalCssImports = importTargets.criticalFiles;
    bundledCssImports = importTargets.bundledFiles;
    cssManifest = {
        bundles: {},
        bundledFiles: [],
        fileBundles: {},
    };

    return { file, source };
}

async function compileAppCss() {
    const { file, source } = refreshAppCssImports();
    const css = inlineImports(source);

    await runPostcss(css, file, path.join(webAssets, criticalCss));
    display(`${path.basename(file)} - ${cssTargetLabel('critical')} -> ${criticalCss}`, 'update');
}

async function compileCss(file) {
    const rel = toPosix(path.relative(src, file));

    if (
        isAppCss(file)
        || isStylesSourceFile(file)
        || bundledCssImports.has(rel)
        || path.basename(file) === legacyFromCss
        || criticalCssImports.has(rel)
    ) return;

    await runPostcss(
        fs.readFileSync(file, 'utf8'),
        file,
        outputPath(file)
    );
    display(toPosix(path.relative(src, file)), 'update');
}

async function compileCssBundle(name) {
    const outputFile = cssBundles[name];

    if (!outputFile) return;

    const out = path.join(webAssets, outputFile);
    const files = [...bundledCssImports]
        .filter(file => bundleGroupForRelativeCss(file) === name)
        .map(file => path.join(src, file))
        .filter(file => fs.existsSync(file) && isCssFile(file))
        .sort();

    if (files.length === 0) {
        if (fs.existsSync(out)) fs.removeSync(out);
        if (fs.existsSync(`${out}.map`)) fs.removeSync(`${out}.map`);
        delete cssManifest.bundles[name];
        return;
    }

    const css = files
        .map(file => rewriteCssUrls(fs.readFileSync(file, 'utf8'), file, out))
        .join('\n');

    await runPostcss(css, path.join(src, `${name}.css`), out);
    cssManifest.bundles[name] = outputFile;

    files.forEach(file => {
        const compiled = outputPath(file);

        if (fs.existsSync(compiled)) fs.removeSync(compiled);
        if (fs.existsSync(`${compiled}.map`)) fs.removeSync(`${compiled}.map`);
    });

    display(`${path.basename(sourceAppCss())} - ${cssTargetLabel(name)} -> ${outputFile}`, 'update');
}

async function compileCssBundles() {
    for (const name of Object.keys(cssBundles)) {
        await compileCssBundle(name);
    }

    writeCssManifest();
}

async function compileCssInBundledDirs() {
    for (const dir of bundledCssSourceDirs) {
        const fullDir = path.join(src, dir);

        if (!fs.existsSync(fullDir)) continue;

        for (const file of getFiles(fullDir)) {
            if (isCssFile(file)) {
                await compileCss(file);
            }
        }
    }
}

async function compileCssBundleForFile(file) {
    const relative = toPosix(path.relative(src, file));
    const group = bundleGroupForRelativeCss(relative);

    if (!group) return;

    await compileCssBundle(group);
}

async function compileCssAfterAppCssChange() {
    await compileAppCss();
    await compileCssBundles();
}

async function compileCssAfterStylesChange(file) {
    copyStatic(file);

    const relative = toPosix(path.relative(src, file));

    await compileAppCss();

    if (bundledCssImports.has(relative)) {
        await compileCssBundleForFile(file);
    }
}

async function compileJs(file) {
    const out = outputPath(file);

    if (isAppJs(file)) {
        const moduleVersions = isProd ? moduleVersionMap() : {};

        await esbuild.build({
            entryPoints: [file],
            outfile: out,
            bundle: true,
            format: 'esm',
            platform: 'browser',
            target: ['es2020'],
            minify: isProd,
            drop: isProd ? ['console', 'debugger'] : [],
            define: {
                __MODULE_VERSIONS__: JSON.stringify(moduleVersions),
            },
            plugins: [appBundlePlugin],
        });
        display(toPosix(path.relative(src, file)), 'update');
        return;
    }

    const result = babel.transformFileSync(file, {
        minified: isProd,
        comments: false,
        sourceMaps: false,
        plugins: [babelAliasPlugin],
        presets: [
            ['@babel/preset-env', {
                bugfixes: true,
                modules: false,
                targets: { esmodules: true },
            }],
        ],
    });

    fs.ensureDirSync(path.dirname(out));
    fs.writeFileSync(out, result.code || '');
    display(toPosix(path.relative(src, file)), 'update');
}

async function compileAppJs() {
    await compileJs(path.join(src, 'app.js'));
}

function appJsDependencyFiles() {
    const entry = path.join(src, 'app.js');
    const visited = new Set();
    const dependencies = new Set();

    function visit(file) {
        const resolvedFile = path.resolve(file);

        if (visited.has(resolvedFile) || !fs.existsSync(resolvedFile)) return;
        visited.add(resolvedFile);

        for (const dependency of jsDependencies(resolvedFile)) {
            dependencies.add(dependency);
            visit(dependency);
        }
    }

    visit(entry);

    return dependencies;
}

function shouldCompileAppJsAfterJsChange(file) {
    if (isProd) return true;

    return appJsDependencyFiles().has(path.resolve(file));
}

function jsImportSpecifiers(file) {
    const source = fs.readFileSync(file, 'utf8');
    const specifiers = [];
    const importExportPattern = /(?:import|export)\s+(?:[^'"]*?\s+from\s*)?["']([^"']+)["']/g;

    for (const match of source.matchAll(importExportPattern)) {
        specifiers.push(match[1]);
    }

    return specifiers;
}

function jsDependencies(file) {
    return jsImportSpecifiers(file)
        .map(specifier => resolveJsImport(file, specifier))
        .filter(Boolean)
        .map(dependency => path.resolve(dependency));
}

function sourceFiles() {
    return currentSourceFiles || getFiles(src);
}

function sourceJsFiles(files = sourceFiles()) {
    return files
        .filter(file => isJsFile(file))
        .filter(file => !isAppJs(file))
        .filter(file => !isVendorFile(file));
}

function jsDependencyMap(files) {
    const map = new Map();

    files.forEach(file => {
        map.set(path.resolve(file), jsDependencies(file));
    });

    return map;
}

async function compileAllJsModules() {
    const files = sourceJsFiles().map(file => path.resolve(file));
    const fileSet = new Set(files);
    const dependencyMap = jsDependencyMap(files);
    const visited = new Set();

    async function visit(file) {
        if (visited.has(file)) return;

        visited.add(file);

        for (const dependency of dependencyMap.get(file) || []) {
            if (fileSet.has(dependency)) {
                await visit(dependency);
            }
        }

        await compileJs(file);
    }

    for (const file of files) {
        await visit(file);
    }
}

function jsImportersOf(targetFile, files = sourceJsFiles().map(file => path.resolve(file)), dependencyMap = jsDependencyMap(files)) {
    const target = path.resolve(targetFile);

    return files
        .filter(file => file !== target)
        .filter(file => (dependencyMap.get(file) || []).includes(target));
}

async function compileJsImporters(file, visited = new Set(), files = null, dependencyMap = null) {
    const target = path.resolve(file);

    if (visited.has(target)) return;
    visited.add(target);

    files = files || sourceJsFiles().map(item => path.resolve(item));
    dependencyMap = dependencyMap || jsDependencyMap(files);

    for (const importer of jsImportersOf(target, files, dependencyMap)) {
        await compileJs(importer);
        await compileJsImporters(importer, visited, files, dependencyMap);
    }
}

async function compileJsWithDependents(file) {
    await compileJs(file);

    if (isProd) {
        await compileJsImporters(file);
    }

    if (shouldCompileAppJsAfterJsChange(file)) {
        await compileAppJs();
    }
}

function moduleVersionMap() {
    const versions = {};
    const moduleRoots = ['common', 'components', 'form', 'heros', 'modules', 'strates'];

    moduleRoots.forEach(rootDir => {
        const directory = path.join(webAssets, rootDir);

        if (!fs.existsSync(directory)) return;

        getFiles(directory).forEach(file => {
            if (!isJsFile(file)) return;

            versions[toPosix(path.relative(webAssets, file))] = fileHash(file);
        });
    });

    return versions;
}

function assetVersionMap() {
    const versions = {};

    if (!fs.existsSync(webAssets)) return versions;

    getFiles(webAssets).forEach(file => {
        const relative = toPosix(path.relative(webAssets, file));

        if (!versionedAssetExtensions.has(path.extname(file).toLowerCase())) return;

        versions[relative] = fileHash(file);
    });

    return versions;
}

function writeBuildManifest() {
    fs.ensureDirSync(webAssets);
    fs.writeJsonSync(path.join(webAssets, buildManifestFile), {
        production: isProd,
        versions: isProd ? assetVersionMap() : {},
    }, { spaces: 2 });
}

function copyStatic(file) {
    const out = outputPath(file);
    fs.ensureDirSync(path.dirname(out));
    fs.copySync(file, out, { filter: source => !isIgnored(source) });
    display(toPosix(path.relative(src, file)), 'update');
}

function syncCopiedDirs() {
    for (const entry of fs.readdirSync(src)) {
        const from = path.join(src, entry);

        if (!fs.statSync(from).isDirectory() || excludedCopyDirs.has(entry)) {
            continue;
        }

        const to = path.join(webAssets, entry);
        fs.removeSync(to);
        fs.copySync(from, to, { filter: source => !isIgnored(source) });
        display(entry, 'update');
    }
}

async function compileJsInDir(dir) {
    for (const file of getFiles(dir)) {
        if (path.extname(file) === '.js') {
            await compileJs(file);
        }
    }
}

async function compileCssInDir(dir) {
    for (const file of getFiles(dir)) {
        if (isCssFile(file)) {
            await compileCss(file);
        }
    }
}

async function compileFile(file) {
    if (isIgnored(file) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) return;
    if (isVendorFile(file)) return;

    const ext = path.extname(file);
    if (isTemplateFile(file)) {
        copyStatic(file);
        return;
    }

    if (ext === '.css') {
        await compileCss(file);
        return;
    }

    if (ext === '.js') {
        await compileJs(file);
        return;
    }

    if (ext !== '.php') {
        copyStatic(file);
    }
}

function getFiles(dir) {
    return fs.readdirSync(dir).flatMap(entry => {
        const file = path.join(dir, entry);
        if (isIgnored(file)) return [];
        return fs.statSync(file).isDirectory() ? getFiles(file) : [file];
    });
}

function snapshotSourceFiles() {
    const snapshot = new Map();

    if (!fs.existsSync(src)) return snapshot;

    for (const file of getFiles(src)) {
        if (isIgnored(file)) continue;

        const stat = fs.statSync(file);
        snapshot.set(path.resolve(file), `${stat.mtimeMs}:${stat.size}`);
    }

    return snapshot;
}

function rebuildSignature(file) {
    if (!fs.existsSync(file)) return 'missing';

    const stat = fs.statSync(file);
    const type = stat.isDirectory() ? 'dir' : 'file';

    return `${type}:${stat.mtimeMs}:${stat.size}`;
}

async function build({ clean = true } = {}) {
    currentSourceFiles = getFiles(src);

    try {
        if (clean) {
            cleanWebAssets();
        } else {
            fs.ensureDirSync(webAssets);
        }

        syncCopiedDirs();

        refreshAppCssImports();

        for (const file of currentSourceFiles) {
            if (isAppCss(file)) continue;
            if (isAppJs(file)) continue;
            if (isJsFile(file)) continue;
            if (isCopiedDirFile(file) && path.extname(file) !== '.js' && !isCssFile(file)) continue;
            await compileFile(file);
        }

        await compileAllJsModules();
        await compileAppJs();
        await compileAppCss();
        await compileCssBundles();
        writeBuildManifest();
    } finally {
        currentSourceFiles = null;
    }
}

function cleanWebAssets() {
    fs.removeSync(webAssets);
    fs.ensureDirSync(webAssets);
    display('web/assets', 'remove');
}

async function rebuild(file, evt = 'update') {
    const absoluteFile = path.isAbsolute(file) ? file : path.join(root, file);
    const exists = fs.existsSync(absoluteFile);

    if (!exists) {
        const out = outputPath(absoluteFile);
        if (fs.existsSync(out)) fs.removeSync(out);
        if (fs.existsSync(`${out}.map`)) fs.removeSync(`${out}.map`);
        if (isCssFile(absoluteFile) && bundledCssSourceDirs.has(topLevelDir(absoluteFile))) {
            await compileCssBundleForFile(absoluteFile);
        }
        if (isAppCss(absoluteFile) || isStylesSourceFile(absoluteFile)) {
            await compileAppCss();
        }
        if (isJsFile(absoluteFile) && !isAppJs(absoluteFile)) {
            if (isProd) {
                await compileJsImporters(absoluteFile);
                await compileAppJs();
            }
        }
        display(toPosix(path.relative(src, absoluteFile)), 'remove');
        return;
    }

    if (isAppCss(absoluteFile)) {
        await compileCssAfterAppCssChange();
        return;
    }

    if (isStylesSourceFile(absoluteFile)) {
        await compileCssAfterStylesChange(absoluteFile);
        return;
    }

    if (fs.statSync(absoluteFile).isDirectory()) {
        if (isCopiedDirFile(absoluteFile)) {
            copyStatic(absoluteFile);
            if (!isVendorFile(absoluteFile)) {
                await compileJsInDir(absoluteFile);
                await compileCssInDir(absoluteFile);
                if (isProd) {
                    await compileAppJs();
                }
            }
        }

        if (bundledCssSourceDirs.has(topLevelDir(absoluteFile))) {
            await compileCssBundleForFile(absoluteFile);
        }

        return;
    }

    if (bundledCssImports.has(toPosix(path.relative(src, absoluteFile)))) {
        await compileCssBundleForFile(absoluteFile);
        return;
    }

    if (isCopiedDirFile(absoluteFile)) {
        if (isVendorFile(absoluteFile)) {
            copyStatic(absoluteFile);
            return;
        }

        if (path.extname(absoluteFile) === '.js') {
            await compileJsWithDependents(absoluteFile);
            return;
        }

        if (path.extname(absoluteFile) === '.css') {
            await compileCss(absoluteFile);
            return;
        }

        copyStatic(absoluteFile);

        return;
    }

    await compileFile(absoluteFile);

    if (path.extname(absoluteFile) === '.css') {
        const relative = toPosix(path.relative(src, absoluteFile));

        if (bundledCssImports.has(relative)) {
            await compileCssBundleForFile(absoluteFile);
        }
    }

    if (isJsFile(absoluteFile) && !isAppJs(absoluteFile)) {
        if (isProd) {
            await compileJsImporters(absoluteFile);
        }

        if (shouldCompileAppJsAfterJsChange(absoluteFile)) {
            await compileAppJs();
        }
    }
}

function scheduleRebuild(evt, file) {
    const absoluteFile = path.isAbsolute(file) ? file : path.join(root, file);
    const key = path.resolve(absoluteFile);
    const signature = rebuildSignature(absoluteFile);

    if (rebuildSignatures.get(key) === signature) {
        return;
    }

    rebuildSignatures.set(key, signature);

    if (fs.existsSync(absoluteFile) && fs.statSync(absoluteFile).isFile()) {
        const stat = fs.statSync(absoluteFile);
        sourceSnapshot.set(key, `${stat.mtimeMs}:${stat.size}`);
    } else {
        sourceSnapshot.delete(key);
    }

    clearTimeout(rebuildTimers.get(key));
    rebuildTimers.set(key, setTimeout(() => {
        rebuildTimers.delete(key);
        rebuild(absoluteFile, evt)
            .then(() => writeBuildManifest())
            .catch(error => {
                display(path.basename(absoluteFile), 'error');
                console.error(error.message);
            });
    }, 75));
}

function scheduleFullBuild(reason = 'watch') {
    clearTimeout(fullBuildTimer);
    fullBuildTimer = setTimeout(() => {
        fullBuildTimer = null;
        build({ clean: false })
            .then(() => display(reason, 'update'))
            .catch(error => {
                display(reason, 'error');
                console.error(error.message);
            });
    }, 300);
}

function startPollingRescan() {
    sourceSnapshot = snapshotSourceFiles();

    setInterval(() => {
        const nextSnapshot = snapshotSourceFiles();

        for (const [file, signature] of nextSnapshot) {
            const previousSignature = sourceSnapshot.get(file);

            if (!previousSignature) {
                scheduleRebuild('add', file);
                continue;
            }

            if (previousSignature !== signature) {
                scheduleRebuild('update', file);
            }
        }

        for (const file of sourceSnapshot.keys()) {
            if (!nextSnapshot.has(file)) {
                scheduleRebuild('remove', file);
            }
        }

        sourceSnapshot = nextSnapshot;
    }, 1000);
}

build()
    .then(() => {
        if (!shouldWatch) {
            if (isProd) {
                console.log("I'm watching you...");
            }

            return;
        }

        console.log("I'm watching you...");
        displayBuildStatus = true;

        chokidar
            .watch(src, {
                ignoreInitial: true,
                usePolling: true,
                interval: 150,
                binaryInterval: 300,
                awaitWriteFinish: {
                    stabilityThreshold: 250,
                    pollInterval: 100,
                },
                ignored: [
                    '**/.DS_Store',
                    '**/node_modules/**',
                    path.join(webAssets, '**'),
                ],
            })
            .on('add', file => scheduleRebuild('add', file))
            .on('change', file => scheduleRebuild('update', file))
            .on('unlink', file => scheduleRebuild('remove', file))
            .on('addDir', directory => {
                scheduleRebuild('add', directory);
                scheduleFullBuild('addDir');
            })
            .on('unlinkDir', directory => {
                scheduleRebuild('remove', directory);
                scheduleFullBuild('unlinkDir');
            })
            .on('error', error => {
                display('watch', 'error');
                console.error(error.message);
            });

    })
    .catch(error => {
        display('build', 'error');
        console.error(error.message);
        process.exitCode = 1;
    });
