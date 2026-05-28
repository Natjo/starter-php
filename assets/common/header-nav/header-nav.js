export default function HeaderNav(root = document) {
    if (root === document && document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            HeaderNav();
        }, { once: true });
        return;
    }



    const navs = root instanceof Element && root.matches('.header-nav')
        ? [root]
        : root.querySelectorAll('.header-nav');

    navs.forEach(el => {
        if (el.dataset.headerNavHydrated === "true") return;

        const toggle = el.querySelector('.header-nav-toggle');

        if (!toggle) return;

        toggle.addEventListener('click', () => {
            const isOpen = el.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });

        el.dataset.headerNavHydrated = "true";
    });
}
