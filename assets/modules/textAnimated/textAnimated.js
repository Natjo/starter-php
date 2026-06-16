import { ScrollDriver, style, stagger } from "@modules/scrollDriver";
import { splitText } from "@modules/splitText";

function textAnimated(el) {
    const title = el?.querySelector?.(".text-animated");
    if (!title) return;

    const driver = new ScrollDriver();
    const { chars, restore } = splitText(title, { skipSelector: ".tooltip" });
    const getViewportProgress = () => {
        const rect = title.getBoundingClientRect();
        const start = window.innerHeight * 0.85;
        const end = window.innerHeight * 0.60 - rect.height;
        const progress = ((start - rect.top) / ((start - end) || 1)) * 100;

        return Math.max(0, Math.min(100, progress));
    };

    stagger(chars, 0, { rootEl: title });
    title.classList.add("is-text-animated-ready");

    driver.add(title, [85, 60], () => {
        stagger(chars, getViewportProgress(), { rootEl: title });
    });

    driver.enable();

    return () => {
        driver.destroy();
        title.classList.remove("is-text-animated-ready");
        style.clear(title);
        chars.forEach(style.clear);
        restore();
    };
}

export default textAnimated;
