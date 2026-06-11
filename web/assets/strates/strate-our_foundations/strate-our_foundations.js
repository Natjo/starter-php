import { ScrollDriver, style, stagger } from "../../modules/scrollDriver/scrollDriver.js";
import { splitText } from "../../modules/splitText/splitText.js";
export default el => {
  var _el$querySelector, _el$querySelectorAll;
  const items = [...el.querySelectorAll(".item")];
  if (!items.length) return;
  const driver = new ScrollDriver();
  const easeOut = t => 1 - Math.pow(1 - t, 3);
  const clamp = value => Math.max(0, Math.min(1, value));
  items.forEach(item => {
    style.opacity(item, 0);
    style.translate(item, 0, 200, 0);
  });
  driver.add(el, "bottom-bottom", e => {
    e.timeline(0, 100, val => {
      const p = val * 100;
      items.forEach((item, index) => {
        const start = index * 8;
        const end = start + 35;
        const progress = easeOut(clamp((p - start) / (end - start)));
        style.opacity(item, progress);
        style.translate(item, 0, `${(1 - progress) * 200}px`, 0);
      });
    });
  });
  const title = el === null || el === void 0 || (_el$querySelector = el.querySelector) === null || _el$querySelector === void 0 ? void 0 : _el$querySelector.call(el, ".title-animate");
  const {
    chars,
    restore
  } = splitText(title);
  const characters = el === null || el === void 0 || (_el$querySelectorAll = el.querySelectorAll) === null || _el$querySelectorAll === void 0 ? void 0 : _el$querySelectorAll.call(el, ".char");
  driver.add(title, [85, 60], e => {
    e.timeline(0, 100, val => stagger(characters, val));
  });
  driver.enable();
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  const images = el.querySelectorAll("picture");
  items.forEach((item, index) => {
    item.addEventListener("mouseenter", () => {
      images[index].classList.add("active");
    });
    item.addEventListener("mouseleave", () => {
      images[index].classList.remove("active");
    });
  });
  return () => {
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    driver.disable();
    items.forEach(item => {
      item.style.opacity = "";
      item.style.transform = "";
    });
  };
};