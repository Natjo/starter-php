class Navanchor {
  constructor(el) {
    this.el = el;
    this.items = Array.from(el.querySelectorAll('a[href^="#"]')).map(link => {
      const id = decodeURIComponent(link.getAttribute("href").slice(1));
      const section = document.getElementById(id);
      return section ? {
        link,
        section
      } : null;
    }).filter(Boolean);
    this.scrollFrame = null;
    if (!this.items.length) return;
    this.onScroll = this.onScroll.bind(this);
    window.addEventListener("scroll", this.onScroll, {
      passive: true
    });
    this.onScroll();
  }
  onScroll() {
    if (this.scrollFrame) return;
    this.scrollFrame = window.requestAnimationFrame(() => {
      this.scrollFrame = null;
      this.update();
    });
  }
  update() {
    let activeIndex = -1;
    this.items.forEach(({
      section
    }) => {
      if (section.getBoundingClientRect().top < window.innerHeight / 2) {
        activeIndex++;
      }
    });
    if (activeIndex < 0) {
      activeIndex = 0;
    }
    this.items.forEach(({
      link
    }, i) => {
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
    if (this.scrollFrame) {
      window.cancelAnimationFrame(this.scrollFrame);
      this.scrollFrame = null;
    }
  }
}
export default el => {
  if (!(el instanceof Element)) return null;
  if (el.__navanchorInstance) return el.__navanchorInstance;
  el.__navanchorInstance = new Navanchor(el);
  return el.__navanchorInstance;
};