import { ScrollDriver, style, stagger } from "@modules/scrollDriver";

export default el => {
    const platforms = el?.querySelectorAll?.(".platform");
    if (!platforms?.length) return;

    style.var(el, platforms.length, "--nb-platforms");

    const driver = new ScrollDriver();
    const easeOut = t => 1 - Math.pow(1 - t, 3);
    const clamp = value => Math.max(0, Math.min(1, value));
    const segments = Math.max(1, platforms.length - 1);

    const items = [...platforms].map(platform => ({
        content: platform.querySelector(".platform-content"),
        inner: platform.querySelector(".platform-content-inner"),
    }));

    let maxHeights = items.map(() => 0);
    const measure = () => {
        maxHeights = items.map(item => item.inner?.offsetHeight || 0);
    };

    const reapply = () => driver.onScroll(window.lenis?.animatedScroll ?? window.scrollY ?? 0);

    driver.add(el, "top-bottom", e => {
        e.timeline(0, 100, val => {
            const seg = 1 / segments;
            const segIndex = Math.min(segments - 1, Math.floor(val / seg));
            const t = easeOut(clamp((val - segIndex * seg) / seg));

            items.forEach((item, index) => {
                let height = 0;
                if (index === segIndex) height = (1 - t) * maxHeights[index];
                else if (index === segIndex + 1) height = t * maxHeights[index];

                style.var(item.content, `${height}px`, "--height");
            });
        });
    });

    driver.enable();

    measure();

    // Recalcule la hauteur réelle après chargement des images / resize.
    const ro = new ResizeObserver(() => {
        measure();
        reapply();
    });
    items.forEach(item => item.inner && ro.observe(item.inner));

    const lenis = window.lenis;
    const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
    lenis?.on("scroll", onLenisScroll);

    reapply();

    return () => {
        lenis?.off("scroll", onLenisScroll);
        driver.disable();
        ro.disconnect();
        items.forEach(item => item.content?.style.removeProperty("--height"));
    };
};
