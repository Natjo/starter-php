export default el => {
    const slides = [...el.querySelectorAll(".slideshow-slide")];
    if (slides.length < 2) return;

    const N = slides.length;
    const INTERVAL = parseInt(el.dataset.interval, 10) || 3000;
    const DURATION = parseInt(el.dataset.duration, 10) || 700;
    const OFFSET = 20; // translate de la slide entrante (px)

    el.style.setProperty("--slideshow-duration", `${DURATION}ms`);

    let current = 0;
    let stopped = false;
    let timerId = null;
    let finalizeId = null;

    // Promeut les data-src / data-srcset en attributs réels et résout une fois
    // l'image chargée (ne charge qu'une seule fois, puis résout immédiatement).
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

    const wipeTo = upcoming => new Promise(resolve => {
        // Slide courante au-dessus (elle se balaye), suivante juste en dessous.
        slides.forEach(slide => (slide.style.zIndex = "0"));
        slides[upcoming].style.zIndex = "1";
        slides[current].style.zIndex = "2";

        // Prépare la slide entrante : décalée, sans transition (pas de saut animé).
        const incoming = slides[upcoming];
        incoming.style.setProperty("--width", "100%");
        incoming.style.transition = "none";
        incoming.style.transform = `translateX(${OFFSET}px)`;
        void incoming.offsetWidth; // force reflow
        incoming.style.transition = "";

        // Balayage : --width 100% -> 0 révèle la slide ; l'entrante glisse vers 0.
        slides[current].style.setProperty("--width", "0%");
        incoming.style.transform = "translateX(0px)";

        finalizeId = window.setTimeout(() => {
            // Slide sortante masquée : on la renvoie derrière et on la réinitialise.
            slides[current].style.zIndex = "0";
            slides[current].style.setProperty("--width", "100%");
            slides[current].style.transform = "";

            current = upcoming;
            slides[current].style.zIndex = "2";
            resolve();
        }, DURATION);
    });

    const tick = async () => {
        if (stopped) return;

        const upcoming = (current + 1) % N;

        // On attend que l'image soit prête (déjà préchargée en arrière-plan
        // pendant l'affichage de la slide) avant de lancer le balayage.
        await loadSlide(upcoming);
        if (stopped) return;

        await wipeTo(upcoming);
        if (stopped) return;

        // La nouvelle slide est active : on précharge déjà la suivante.
        loadSlide((current + 1) % N);

        timerId = window.setTimeout(tick, INTERVAL);
    };

    // État initial : seule la 1ère slide est visible au-dessus.
    slides.forEach(slide => (slide.style.zIndex = "0"));
    slides[current].style.zIndex = "2";

    // Précharge la 2ème slide dès maintenant (pendant l'affichage de la 1ère).
    loadSlide((current + 1) % N);

    timerId = window.setTimeout(tick, INTERVAL);

    return () => {
        stopped = true;
        window.clearTimeout(timerId);
        window.clearTimeout(finalizeId);
        slides.forEach(slide => {
            slide.style.removeProperty("z-index");
            slide.style.removeProperty("--width");
            slide.style.removeProperty("transform");
            slide.style.removeProperty("transition");
        });
    };
};
