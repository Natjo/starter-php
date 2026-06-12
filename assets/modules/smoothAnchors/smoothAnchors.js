const easeInOutCubic = t => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);

/**
 * Scroll fluide (ease-in-out) vers les ancres internes, et retour en haut.
 *
 * Utilise window.lenis si disponible, sinon fallback natif `scroll-behavior: smooth`.
 * Délégation d'événements : fonctionne aussi avec des liens ajoutés dynamiquement.
 *
 * Un lien est considéré comme "retour en haut" si son href vaut `#`, `#top`,
 * ou la racine `/` (intercepté uniquement sur la home pour préserver la
 * navigation inter-pages).
 *
 * @param {Object}  [options]
 * @param {string}  [options.links='a[href]']         Sélecteur des liens gérés.
 * @param {number}  [options.duration=1.2]            Durée (s) pour Lenis.
 * @param {Function}[options.easing=easeInOutCubic]   Easing pour Lenis.
 * @returns {Function} cleanup
 */
export default function smoothAnchors(options = {}) {
    const {
        links = 'a[href]',
        duration = 1.2,
        easing = easeInOutCubic,
    } = options;

    const scrollToTarget = target => {
        const isNumber = typeof target === "number";

        if (window.lenis) {
            window.lenis.scrollTo(target, { duration, easing });
        } else if (isNumber) {
            window.scrollTo({ top: target, behavior: "smooth" });
        } else {
            target.scrollIntoView({ behavior: "smooth" });
        }
    };

    const getAnchorTarget = href => {
        const i = href.indexOf("#");
        if (i === -1) return null;
        const id = decodeURIComponent(href.slice(i + 1));
        return id ? document.getElementById(id) : null;
    };

    const onClick = event => {
        const node = event.target;
        if (!node || typeof node.closest !== "function") return;

        const link = node.closest(links);
        if (!link || !document.contains(link)) return;

        const href = link.getAttribute("href") || "";

        // Retour en haut : href "#", "#top", ou racine "/" (home uniquement).
        if (href === "#" || href === "#top" || href === "/") {
            if (href === "/" && window.location.pathname !== "/") return;

            event.preventDefault();
            scrollToTarget(0);
            history.pushState(null, "", window.location.pathname + window.location.search);
            return;
        }

        // Lien d'ancre interne.
        const target = getAnchorTarget(href);
        if (!target) return; // lien externe / autre page / cible absente

        event.preventDefault();
        scrollToTarget(target);
        if (target.id) history.pushState(null, "", `#${target.id}`);
    };

    document.addEventListener("click", onClick);

    return () => document.removeEventListener("click", onClick);
}
