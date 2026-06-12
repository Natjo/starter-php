import { ScrollDriver, style, stagger } from "@modules/scrollDriver";
import { splitText } from "@modules/splitText";

function textAnimated(el) {
    const title = el?.querySelector?.(".text-animated");
    if (!title) return;

    const driver = new ScrollDriver();
    const { chars, restore } = splitText(title);

    driver.add(title, [85, 60], e => {
        e.timeline(0, 100, (val) => stagger(chars, val));
    });

    driver.enable();

    const lenis = window.lenis;
    const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
    lenis?.on("scroll", onLenisScroll);

    return () => {
        driver.disable();
        lenis?.off("scroll", onLenisScroll);
        restore();
    };
}

export default textAnimated;