import { ScrollDriver, style } from "@modules/scrollDriver/scrollDriver";

export default el => {
    const reduceMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;
    const columns = [...el.querySelectorAll(".col")];
    const speeds = [2.6, 3.6, 3.1, 2.75];
    const amplitude = 500;

    if (reduceMotion || !columns.length) return;

    const driver = new ScrollDriver();

    driver.add(el, [100, 0], e => {
        e.timeline(0, 100, val => {
            const centered = val - 0.5;

            columns.forEach((col, index) => {
                const speed = speeds[index] ?? 1;

                style.translate(col, 0, -centered * amplitude * speed, 0);
            });
        });
    });

    driver.enable();

    const lenis = window.lenis;
    const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
    lenis?.on("scroll", onLenisScroll);

    driver.onScroll(lenis?.animatedScroll ?? window.scrollY ?? 0);

    return () => {
        lenis?.off("scroll", onLenisScroll);
        driver.disable();
        columns.forEach(col => {
            col.style.transform = "";
        });
    };
};
