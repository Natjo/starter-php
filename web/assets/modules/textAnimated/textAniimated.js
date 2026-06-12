import { ScrollDriver, style, stagger } from "../scrollDriver/scrollDriver.js";
import { splitText } from "../splitText/splitText.js";
function textAnimated(el) {
  var _el$querySelector;
  const title = el === null || el === void 0 || (_el$querySelector = el.querySelector) === null || _el$querySelector === void 0 ? void 0 : _el$querySelector.call(el, ".text-animated");
  if (!title) return;
  const driver = new ScrollDriver();
  const {
    chars,
    restore
  } = splitText(title);
  driver.add(title, [85, 60], e => {
    e.timeline(0, 100, val => stagger(chars, val));
  });
  driver.enable();
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  return () => {
    driver.disable();
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    restore();
  };
}
export default textAnimated;