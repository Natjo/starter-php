import { ScrollDriver, style } from "@modules/scrollDriver";

export default el => {
    const platforms = el?.querySelectorAll?.(".platform");
    if (!platforms?.length) return;

    style.var(el, platforms.length, "--nb-platforms");

    const driver = new ScrollDriver();
    const clamp = value => Math.max(0, Math.min(1, value));
    const count = platforms.length;
    const segments = Math.max(1, count - 1);

    const items = [...platforms].map(platform => ({
        el: platform,
        content: platform.querySelector(".platform-content"),
        inner: platform.querySelector(".platform-content-inner"),
    }));

    let maxHeights = items.map(() => 0);
    let lastProgress = 0;

    const measure = () => {
        maxHeights = items.map(item => item.inner?.offsetHeight || 0);
    };

    const applyProgress = progress => {
        lastProgress = clamp(progress);

        items.forEach((item, index) => {
            let perone = 0;

            if (count === 1) {
                perone = 1;
            } else if (lastProgress >= 1) {
                perone = index === count - 1 ? 1 : 0;
            } else if (lastProgress <= 0) {
                perone = index === 0 ? 1 : 0;
            } else {
                const raw = lastProgress * segments;
                const current = Math.min(count - 2, Math.floor(raw));
                const t = raw - current;

                if (index === current) perone = 1 - t;
                else if (index === current + 1) perone = t;
            }

            style.var(item.el, perone, "--perone");
            style.var(item.content, `${perone * maxHeights[index]}px`, "--height");
        });
    };

    driver.add(el, "top-bottom", e => {
        e.timeline(0, 100, val => {
            applyProgress(val);
        });
    });

    driver.enable();
    measure();

    const ro = new ResizeObserver(() => {
        measure();
        applyProgress(lastProgress);
    });

    items.forEach(item => item.inner && ro.observe(item.inner));

    const lenis = window.lenis;
    const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
    lenis?.on("scroll", onLenisScroll);
    driver.onScroll(lenis?.animatedScroll ?? window.scrollY ?? 0);

    applyProgress(0);

    return () => {
        lenis?.off("scroll", onLenisScroll);
        driver.disable();
        ro.disconnect();
        el?.style.removeProperty("--nb-platforms");
        items.forEach(item => {
            item.el?.style.removeProperty("--perone");
            item.content?.style.removeProperty("--height");
        });
    };
};
