export default el => {
    const slides = [...el.querySelectorAll(".slideshow-slide")];
    if (slides.length < 2) return;

    const N = slides.length;
    const INTERVAL = parseInt(el.dataset.interval, 10) || 3000;
    const DURATION = parseInt(el.dataset.duration, 10) || 700;
    const OFFSET = 20;
    const easeOut = t => 1 - Math.pow(1 - t, 4);

    el.style.setProperty("--slideshow-duration", `${DURATION}ms`);

    let current = 0;
    let stopped = false;
    let timerId = null;
    let rafId = null;

    const loadSlide = index => new Promise(resolve => {
        const slide = slides[index];

        slide.querySelectorAll("[data-srcset]").forEach(node => {
            node.setAttribute("srcset", node.dataset.srcset);
            delete node.dataset.srcset;
        });
        slide.querySelectorAll("[data-src]").forEach(node => {
            node.setAttribute("src", node.dataset.src);
            delete node.dataset.src;
        });

        const img = slide.querySelector("img");
        if (!img || (img.complete && img.naturalWidth > 0)) {
            resolve();
            return;
        }

        const done = () => {
            img.removeEventListener("load", done);
            img.removeEventListener("error", done);
            resolve();
        };
        img.addEventListener("load", done);
        img.addEventListener("error", done);
    });

    const animateWidth = (slide, from, to) => new Promise(resolve => {
        const start = performance.now();

        const tick = now => {
            if (stopped) {
                resolve();
                return;
            }

            const t = Math.min(1, (now - start) / DURATION);
            const value = from + (to - from) * easeOut(t);

            slide.style.setProperty("--width", `${value}%`);

            if (t < 1) {
                rafId = requestAnimationFrame(tick);
                return;
            }

            rafId = null;
            resolve();
        };

        rafId = requestAnimationFrame(tick);
    });

    const wipeTo = async upcoming => {
        slides.forEach(slide => (slide.style.zIndex = "0"));
        slides[upcoming].style.zIndex = "1";
        slides[current].style.zIndex = "2";

        const outgoing = slides[current];
        const incoming = slides[upcoming];

        outgoing.style.transition = "none";
        outgoing.style.setProperty("--width", "100%");

        incoming.style.setProperty("--width", "100%");
        incoming.style.transition = `transform ${DURATION}ms ease-out`;
        incoming.style.transform = `translateX(${OFFSET}px)`;
        void incoming.offsetWidth;
        incoming.style.transform = "translateX(0px)";

        await animateWidth(outgoing, 100, 0);
        if (stopped) return;

        outgoing.style.zIndex = "0";
        outgoing.style.setProperty("--width", "100%");
        outgoing.style.transform = "";
        outgoing.style.transition = "";

        current = upcoming;
        slides[current].style.zIndex = "2";
    };

    const tick = async () => {
        if (stopped) return;

        const upcoming = (current + 1) % N;

        await loadSlide(upcoming);
        if (stopped) return;

        await wipeTo(upcoming);
        if (stopped) return;

        loadSlide((current + 1) % N);

        timerId = window.setTimeout(tick, INTERVAL);
    };

    slides.forEach(slide => (slide.style.zIndex = "0"));
    slides[current].style.zIndex = "2";

    loadSlide((current + 1) % N);

    timerId = window.setTimeout(tick, INTERVAL);

    return () => {
        stopped = true;
        window.clearTimeout(timerId);
        if (rafId != null) cancelAnimationFrame(rafId);
        slides.forEach(slide => {
            slide.style.removeProperty("z-index");
            slide.style.removeProperty("--width");
            slide.style.removeProperty("transform");
            slide.style.removeProperty("transition");
        });
    };
};
