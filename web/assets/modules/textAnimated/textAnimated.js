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
  return () => {
    driver.destroy();
    style.clear(title);
    chars.forEach(style.clear);
    restore();
  };
}
export default textAnimated;