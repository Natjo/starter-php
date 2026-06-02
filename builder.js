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
const dist = path.join(root, 'dist', 'assets');
const fromCss = 'app.css';
const legacyFromCss = 'styles.css';
const criticalCss = 'critical.css';
const cssBundles = {
    common: 'common.css',
    components: 'components.css',
    modules: 'modules.css',
};
const cssImportTargetNames = new Set(['critical', ...Object.keys(cssBundles)]);
const cssManifestFile = 'css-bundles.json';
const bundledCssSourceDirs = new Set([...Object.keys(cssBundles), 'vendors', 'strates']);
const excludedCopyDirs = new Set(['common', 'components', 'heros', 'modules', 'strates']);
const importAliases = {
    'common': path.join(src, 'common'),
    '@common': path.join(src, 'common'),
    '@components': path.join(src, 'components'),
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
let fullBuildTimer = null;
let sourceSnapshot = new Map();
const shouldWatch = process.argv.includes('--watch');

const processor = postcss([
    cssnano,
    postcssExtendRule,
    postcssGlobalData({ files: [path.join(src, 'styles/customMedias.css')] }),
    cssCustomMedia(),
    autoprefixer({ add: true }),
]);

const toPosix = file => file.split(path.sep).join('/');

const isIgnored = file => path.basename(file) === '.DS_Store';

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
const isJsFile = file => path.extname(file) === '.js';
const isCssFile = file => path.extname(file) === '.css';

const outputPath = file => path.join(dist, path.relative(src, file));

const autoBundleGroupForRelativeCss = file => {
    const dir = file.split('/')[0];

    if (dir === 'vendors') return 'common';
    if (dir === 'strates') return 'common';
    if (file === 'styles/print.css') return 'common';
    if (cssBundles[dir]) return dir;

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

const versionedImport = (from, to) => `${relativeImport(from, to)}?v=${jsVersionHash(to)}`;

const babelAliasPlugin = () => ({
    visitor: {
        'ImportDeclaration|ExportNamedDeclaration|ExportAllDeclaration'(babelPath, state) {
            const source = babelPath.node.source;

            if (!source) return;

            const resolved = resolveJsImport(state.file.opts.filename, source.value);

            if (resolved) {
                source.value = versionedImport(state.file.opts.filename, resolved);
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

        build.onResolve({ filter: /^\.\/(common|components|strates)\// }, args => ({
            path: args.path,
            external: true,
        }));
    },
};

const display = (file, status) => {
    const colors = {
        add: '36m',
        remove: '31m',
        update: '32m',
        error: '31m',
    };
    console.log(`\x1b[1m${file}\x1b[22m`, `\x1b[${colors[status] || '90m'}${status}\x1b[39m`);
};

async function runPostcss(css, from, to) {
    const result = await processor.process(css, {
        from,
        to,
        parser,
        map: { inline: false, annotation: `${path.basename(to)}.map` },
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
        const asset = absolute.startsWith(src) ? path.join(dist, path.relative(src, absolute)) : absolute;
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

        const out = path.join(dist, criticalCss);
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

    fs.ensureDirSync(dist);
    fs.writeJsonSync(path.join(dist, cssManifestFile), cssManifest, { spaces: 2 });
}

async function compileAppCss() {
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
    const css = inlineImports(source);

    await runPostcss(css, file, path.join(dist, criticalCss));
    display(`${path.basename(file)} -> ${criticalCss}`, 'update');
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

    const out = path.join(dist, outputFile);
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

    display(`${name} -> ${outputFile}`, 'update');
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
    await compileCssInBundledDirs();
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
        const moduleVersions = moduleVersionMap();

        await esbuild.build({
            entryPoints: [file],
            outfile: out,
            bundle: true,
            format: 'esm',
            platform: 'browser',
            target: ['es2020'],
            minify: true,
            drop: [],
            define: {
                __MODULE_VERSIONS__: JSON.stringify(moduleVersions),
            },
            plugins: [appBundlePlugin],
        });
        display(toPosix(path.relative(src, file)), 'update');
        return;
    }

    const result = babel.transformFileSync(file, {
        minified: true,
        comments: false,
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

function sourceJsFiles() {
    return getFiles(src)
        .filter(file => isJsFile(file))
        .filter(file => !isAppJs(file))
        .filter(file => !isVendorFile(file));
}

async function compileAllJsModules() {
    const files = sourceJsFiles().map(file => path.resolve(file));
    const fileSet = new Set(files);
    const visited = new Set();

    async function visit(file) {
        if (visited.has(file)) return;

        visited.add(file);

        for (const dependency of jsDependencies(file)) {
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

function jsImportersOf(targetFile) {
    const target = path.resolve(targetFile);

    return sourceJsFiles()
        .map(file => path.resolve(file))
        .filter(file => file !== target)
        .filter(file => jsDependencies(file).includes(target));
}

async function compileJsImporters(file, visited = new Set()) {
    const target = path.resolve(file);

    if (visited.has(target)) return;
    visited.add(target);

    for (const importer of jsImportersOf(target)) {
        await compileJs(importer);
        await compileJsImporters(importer, visited);
    }
}

async function compileJsWithDependents(file) {
    await compileJs(file);
    await compileJsImporters(file);
    await compileAppJs();
}

function moduleVersionMap() {
    const versions = {};
    const moduleRoots = ['common', 'components', 'heros', 'modules', 'strates'];

    moduleRoots.forEach(rootDir => {
        const directory = path.join(dist, rootDir);

        if (!fs.existsSync(directory)) return;

        getFiles(directory).forEach(file => {
            if (!isJsFile(file)) return;

            versions[toPosix(path.relative(dist, file))] = fileHash(file);
        });
    });

    return versions;
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

        const to = path.join(dist, entry);
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

async function build() {
    fs.ensureDirSync(dist);
    syncCopiedDirs();

    await compileAppCss();
    await compileCssBundles();

    for (const file of getFiles(src)) {
        if (isAppCss(file)) continue;
        if (isAppJs(file)) continue;
        if (isJsFile(file)) continue;
        if (isCopiedDirFile(file) && path.extname(file) !== '.js' && !isCssFile(file)) continue;
        await compileFile(file);
    }

    await compileAllJsModules();
    await compileAppJs();
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
            await compileJsImporters(absoluteFile);
            await compileAppJs();
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
                await compileAppJs();
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
        await compileJsImporters(absoluteFile);
        await compileAppJs();
    }
}

function scheduleRebuild(evt, file) {
    const absoluteFile = path.isAbsolute(file) ? file : path.join(root, file);
    const key = path.resolve(absoluteFile);

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
        build()
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
        if (!shouldWatch) return;

        console.log("I'm watching you...");

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
                    path.join(dist, '**'),
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

        startPollingRescan();
    })
    .catch(error => {
        display('build', 'error');
        console.error(error.message);
        process.exitCode = 1;
    });
