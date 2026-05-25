const fs = require('fs-extra');
const path = require('path');
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
const toCss = 'styles.css';
const excludedCopyDirs = new Set(['common', 'components', 'heros', 'modules', 'strates']);
let appCssImports = new Set();
const rebuildTimers = new Map();
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

const topLevelDir = file => {
    const relative = toPosix(path.relative(src, file));
    return relative.split('/')[0];
};

const isCopiedDirFile = file => {
    const dir = topLevelDir(file);
    return Boolean(dir) && !dir.startsWith('..') && !excludedCopyDirs.has(dir) && fs.existsSync(path.join(src, dir)) && fs.statSync(path.join(src, dir)).isDirectory();
};

const isPluginFile = file => topLevelDir(file) === 'plugins';
const isStylesSourceFile = file => topLevelDir(file) === 'styles';

const outputPath = file => path.join(dist, path.relative(src, file));

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

function inlineImports(css = '') {
    return css.replace(/^\s*@import\s+["']([^"']+)["']\s*;?/igm, (match, importedFile) => {
        const importedPath = path.resolve(src, importedFile);

        if (!fs.existsSync(importedPath)) {
            throw new Error(`Import CSS introuvable : ${importedFile}`);
        }

        return `${fs.readFileSync(importedPath, 'utf8')}\n`;
    });
}

function collectImports(css = '') {
    const imports = [];

    css.replace(/^\s*@import\s+["']([^"']+)["']\s*;?/igm, (match, importedFile) => {
        imports.push(toPosix(path.normalize(importedFile)));
    });

    return new Set(imports);
}

async function compileAppCss() {
    const file = sourceAppCss();

    if (!fs.existsSync(file)) {
        display(path.relative(root, file), 'error');
        throw new Error(`Fichier CSS principal introuvable : ${path.relative(root, file)}`);
    }

    const source = fs.readFileSync(file, 'utf8');
    appCssImports = collectImports(source);
    const css = inlineImports(source);

    await runPostcss(css, file, path.join(dist, toCss));
    display(`${path.basename(file)} -> ${toCss}`, 'update');
}

async function compileCss(file) {
    const rel = toPosix(path.relative(src, file));

    if (isAppCss(file) || isStylesSourceFile(file) || path.basename(file) === legacyFromCss || appCssImports.has(rel)) return;

    await runPostcss(
        fs.readFileSync(file, 'utf8'),
        file,
        outputPath(file)
    );
    display(toPosix(path.relative(src, file)), 'update');
}

function compileJs(file) {
    const out = outputPath(file);

    if (path.resolve(file) === path.join(src, 'app.js')) {
        esbuild.buildSync({
            entryPoints: [file],
            outfile: out,
            bundle: true,
            format: 'esm',
            platform: 'browser',
            target: ['es2020'],
            minify: true,
        });
        display(toPosix(path.relative(src, file)), 'update');
        return;
    }

    const result = babel.transformFileSync(file, {
        minified: true,
        comments: false,
        presets: [
            ['@babel/preset-env', {
                bugfixes: true,
                modules: false,
            }],
        ],
    });

    fs.ensureDirSync(path.dirname(out));
    fs.writeFileSync(out, result.code || '');
    display(toPosix(path.relative(src, file)), 'update');
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

function compileJsInDir(dir) {
    for (const file of getFiles(dir)) {
        if (path.extname(file) === '.js') {
            compileJs(file);
        }
    }
}

async function compileFile(file) {
    if (isIgnored(file) || !fs.existsSync(file) || fs.statSync(file).isDirectory()) return;
    if (isPluginFile(file)) return;

    const ext = path.extname(file);
    if (ext === '.css') {
        await compileCss(file);
        return;
    }

    if (ext === '.js') {
        compileJs(file);
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

async function build() {
    fs.ensureDirSync(dist);
    syncCopiedDirs();

    await compileAppCss();

    for (const file of getFiles(src)) {
        if (isCopiedDirFile(file) && path.extname(file) !== '.js') continue;
        await compileFile(file);
    }
}

async function rebuild(file, evt = 'update') {
    const absoluteFile = path.isAbsolute(file) ? file : path.join(root, file);
    const exists = fs.existsSync(absoluteFile);

    if (!exists) {
        const out = outputPath(absoluteFile);
        if (fs.existsSync(out)) fs.removeSync(out);
        await compileAppCss();
        display(toPosix(path.relative(src, absoluteFile)), 'remove');
        return;
    }

    if (isStylesSourceFile(absoluteFile)) {
        copyStatic(absoluteFile);
        await compileAppCss();
        return;
    }

    if (fs.statSync(absoluteFile).isDirectory()) {
        if (isCopiedDirFile(absoluteFile)) {
            copyStatic(absoluteFile);
            if (!isPluginFile(absoluteFile)) {
                compileJsInDir(absoluteFile);
            }
        }

        return;
    }

    if (isCopiedDirFile(absoluteFile)) {
        if (isPluginFile(absoluteFile)) {
            copyStatic(absoluteFile);
            return;
        }

        if (path.extname(absoluteFile) === '.js') {
            compileJs(absoluteFile);
            return;
        }

        copyStatic(absoluteFile);

        if (path.extname(absoluteFile) === '.css') {
            await compileAppCss();
        }

        return;
    }

    await compileFile(absoluteFile);

    if (path.extname(absoluteFile) === '.css') {
        await compileAppCss();
    }
}

function scheduleRebuild(evt, file) {
    const absoluteFile = path.isAbsolute(file) ? file : path.join(root, file);
    const key = path.resolve(absoluteFile);

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

build()
    .then(() => {
        if (!shouldWatch) return;

        console.log("I'm watching you...");

        chokidar
            .watch(src, {
                ignoreInitial: true,
                usePolling: true,
                interval: 150,
                ignored: [
                    '**/.DS_Store',
                    '**/node_modules/**',
                    path.join(dist, '**'),
                ],
            })
            .on('all', scheduleRebuild)
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
