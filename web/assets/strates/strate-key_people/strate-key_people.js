import { ScrollDriver, style, stagger } from "../../modules/scrollDriver/scrollDriver.js";
import textAnimated from "../../modules/textAnimated/textAnimated.js";
export default el => {
  var _ref, _lenis$animatedScroll;
  textAnimated(el);
  const list = el.querySelector('.list');
  const images = el.querySelector('.images');
  const driver = new ScrollDriver();
  driver.add(el, [0, 50], e => {
    e.timeline(0, 100, val => {
      stagger(list, val, {
        softness: 0.2
      });
      stagger(images, val, {
        selector: "img"
      });
    });
  });
  driver.enable();
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  driver.onScroll((_ref = (_lenis$animatedScroll = lenis === null || lenis === void 0 ? void 0 : lenis.animatedScroll) !== null && _lenis$animatedScroll !== void 0 ? _lenis$animatedScroll : window.scrollY) !== null && _ref !== void 0 ? _ref : 0);
  return () => {
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    driver.disable();
  };
};