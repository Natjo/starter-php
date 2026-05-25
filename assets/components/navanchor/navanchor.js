class Navanchor {
    constructor(el) {
        this.el = el;
        this.links = Array.from(el.querySelectorAll('a[href^="#"]'));
        this.sections = document.querySelectorAll(".strate[id]");

        if (!this.links.length || !this.sections.length) return;

        this.onScroll = this.onScroll.bind(this);
        window.addEventListener("scroll", this.onScroll, { passive: true });
        this.onScroll();
    }

    onScroll() {
        let activeIndex = -1;

        this.sections.forEach((section) => {
            if (section.getBoundingClientRect().top < window.innerHeight / 2) {
                activeIndex++;
            }
        });

        if (activeIndex < 0) {
            activeIndex = 0;
        }

        this.links.forEach((link, i) => {
            const isActive = i === activeIndex;
            link.classList.toggle("active", isActive);
            if (isActive) {
                link.setAttribute("aria-current", "location");
            } else {
                link.removeAttribute("aria-current");
            }
        });
    }

    destroy() {
        window.removeEventListener("scroll", this.onScroll);
    }
}

export default (el) => {
    if (!(el instanceof Element)) return null;
    return new Navanchor(el);
};
