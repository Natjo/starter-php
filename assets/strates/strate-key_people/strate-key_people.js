import { ScrollDriver, style, stagger } from "@modules/scrollDriver";
import textAnimated from "@modules/textAnimated";

export default el => {
    const destroyText = textAnimated(el);
    const list = el.querySelector('.list');
    const images = el.querySelector('.images');
    
    if (!list || !images) return destroyText;

    stagger(list, 0, { softness: 0.2 });
    stagger(images, 0, { selector: "img" });

    const driver = new ScrollDriver();
    driver.add(el, [0, 50], e => {
        e.timeline(0, 100, val => {
            stagger(list, val, { softness: 0.2 });
            stagger(images, val, { selector: "img" });
        });
    });

    driver.enable();

    return () => {
        destroyText?.();
        driver.destroy();
        style.clear(list);
        style.clear(images);
    };
}
