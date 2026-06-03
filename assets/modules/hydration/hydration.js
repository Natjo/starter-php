const moduleVersions = __MODULE_VERSIONS__;
const moduleCache = new Map();

const versionedModulePath = modulePath => {
    const version = moduleVersions[modulePath.replace(/^\.\//, '')];

    return version ? `${modulePath}?v=${version}` : modulePath;
};

const moduleCandidates = (moduleName) => {
    const parts = moduleName.split('/').filter(Boolean);
    const last = parts[parts.length - 1];

    return [
        last ? `${moduleName}/${last}` : null,
        moduleName,
    ].filter(Boolean);
};

const importModule = async (moduleName) => {
    let lastError;

    for (const candidate of moduleCandidates(moduleName)) {
        const [dir, ...rest] = candidate.split('/');
        const file = rest.join('/');
        let modulePath = "";

        try {
            if (dir === 'common') modulePath = `./common/${file}.js`;
            if (dir === 'components') modulePath = `./components/${file}.js`;
            if (dir === 'modules') modulePath = `./modules/${file}.js`;
            if (dir === 'strates') modulePath = `./strates/${file}.js`;

            if (modulePath) {
                modulePath = versionedModulePath(modulePath);

                if (!moduleCache.has(modulePath)) {
                    moduleCache.set(modulePath, import(modulePath));
                }

                return await moduleCache.get(modulePath);
            }

            throw new Error(`Préfixe de module inconnu : ${dir}`);
        } catch (error) {
            lastError = error;
        }
    }

    throw lastError;
};

const hydrateModule = (el) => {
    if (el.dataset.moduleHydrated === "true") return;

    const moduleName = el.dataset.module;

    if (!moduleName) return;

    importModule(moduleName)
        .then(module => {
            const hydrate = module.default;

            if (typeof hydrate === 'function') {
                hydrate(el);
                el.dataset.moduleHydrated = "true";
            }
        })
        .catch(error => {
            console.error(`Module introuvable : ${moduleName}`, error);
        });
};

const shouldHydrateOnVisible = (el) => /\B@visible\s+true\b/.test(el.dataset.context || "");

export default function ModulesHydration() {
    const modules = document.querySelectorAll('[data-module]');

    if (modules.length === 0) return;

    const visibleObserver = "IntersectionObserver" in window
        ? new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                visibleObserver.unobserve(entry.target);
                hydrateModule(entry.target);
            });
        }, { rootMargin: "200px 0px" })
        : null;

    modules.forEach((el) => {
        if (shouldHydrateOnVisible(el)) {
            if (visibleObserver) {
                visibleObserver.observe(el);
                return;
            }
        }

        hydrateModule(el);
    });
}
