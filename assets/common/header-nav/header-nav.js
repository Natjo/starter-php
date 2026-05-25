export default el => {
    const toggle = el.querySelector('.header-nav-toggle');

    if (!toggle) return;

    toggle.addEventListener('click', () => {
        const isOpen = el.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });
};
