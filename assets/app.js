import Lenis from "./plugins/lenis/lenis.mjs";

const lenis = new Lenis({
    autoRaf: false,
});

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}

requestAnimationFrame(raf);

const modules = document.querySelectorAll('[data-module]');

modules.forEach(el => {

    if (el.dataset.moduleHydrated === "true") return;

    const moduleName = el.dataset.module;

    if (!moduleName) return;

    import(`./${moduleName}.js`)
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
});
