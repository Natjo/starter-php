import { ScrollDriver, style, stagger } from "@modules/scrollDriver";
import { splitText } from "@modules/splitText";

function textAnimated(el) {
    const title = el?.querySelector?.(".text-animated");
    if (!title) return;

    const driver = new ScrollDriver();
    const { chars, restore } = splitText(title);

    const items = title?.querySelectorAll?.(".char");

    driver.add(title, [85, 25], e => {
        e.timeline(0, 100, (val) => stagger(items, val));
    });

    driver.enable();

    return () => {
        driver.destroy();
        style.clear(title);
        chars.forEach(style.clear);
        restore();
    };
}

export default textAnimated;
