import { ScrollDriver, style, stagger } from "@modules/scrollDriver";
import textAnimated from "@modules/textAnimated";

export default el => {

    textAnimated(el);

    const list = el.querySelector('.list');
    const images = el.querySelector('.images');
    
    const driver = new ScrollDriver();
    driver.add(el, [0, 50], e => {
        e.timeline(0, 100, val => {
            stagger(list, val, { softness: 0.2 });
            stagger(images, val, { selector: "img" });
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
    };
}
