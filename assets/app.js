import Lenis from "@vendors/lenis/lenis";

const lenis = new Lenis({
    autoRaf: false,
});

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}

requestAnimationFrame(raf);

const modules = document.querySelectorAll('[data-module]');

const moduleCandidates = moduleName => {
    const parts = moduleName.split('/').filter(Boolean);
    const last = parts[parts.length - 1];

    return [
        moduleName,
        last ? `${moduleName}/${last}` : null,
    ].filter(Boolean);
};

const importModule = async moduleName => {
    let lastError;

    for (const candidate of moduleCandidates(moduleName)) {
        const [dir, ...rest] = candidate.split('/');
        const file = rest.join('/');
        let modulePath = "";

        try {
            if (dir === 'common') modulePath = `./common/${file}.js`;
            if (dir === 'components') modulePath = `./components/${file}.js`;
            if (dir === 'strates') modulePath = `./strates/${file}.js`;

            if (modulePath) {
                return await import(modulePath);
            }

            throw new Error(`Préfixe de module inconnu : ${dir}`);
        } catch (error) {
            lastError = error;
        }
    }

    throw lastError;
};

const hydrateModule = el => {
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

const shouldHydrateOnVisible = el => {
    return (el.dataset.context || "").split(/\s+/).includes("@visible")
        && (el.dataset.context || "").split(/\s+/).includes("true");
};

const visibleObserver = "IntersectionObserver" in window
    ? new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            visibleObserver.unobserve(entry.target);
            hydrateModule(entry.target);
        });
    })
    : null;

modules.forEach(el => {
    if (shouldHydrateOnVisible(el)) {
        if (visibleObserver) visibleObserver.observe(el);
        return;
    }

    hydrateModule(el);
});
