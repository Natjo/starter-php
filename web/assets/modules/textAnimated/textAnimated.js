import { ScrollDriver, style, stagger } from "../scrollDriver/scrollDriver.js";
import { splitText } from "../splitText/splitText.js";
function textAnimated(el) {
  var _el$querySelector, _title$querySelectorA;
  const title = el === null || el === void 0 || (_el$querySelector = el.querySelector) === null || _el$querySelector === void 0 ? void 0 : _el$querySelector.call(el, ".text-animated");
  if (!title) return;
  const driver = new ScrollDriver();
  const {
    chars,
    restore
  } = splitText(title);
  const items = title === null || title === void 0 || (_title$querySelectorA = title.querySelectorAll) === null || _title$querySelectorA === void 0 ? void 0 : _title$querySelectorA.call(title, ".char");
  driver.add(title, [85, 25], e => {
    e.timeline(0, 100, val => stagger(items, val));
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