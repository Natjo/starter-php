import { ScrollDriver, style } from "../../modules/scrollDriver/scrollDriver.js";
export default el => {
  var _window$matchMedia, _window, _ref, _lenis$animatedScroll;
  const reduceMotion = (_window$matchMedia = (_window = window).matchMedia) === null || _window$matchMedia === void 0 ? void 0 : _window$matchMedia.call(_window, "(prefers-reduced-motion: reduce)").matches;
  const columns = [...el.querySelectorAll("[data-speed]")];
  if (reduceMotion || !columns.length) return;
  const driver = new ScrollDriver();
  const amplitude = 160;
  driver.add(el, [100, 0], e => {
    e.timeline(0, 100, val => {
      const centered = val - 0.5;
      columns.forEach(col => {
        const speed = parseFloat(col.dataset.speed) || 0;
        style.translate(col, 0, -centered * amplitude * speed, 0);
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
    columns.forEach(col => {
      col.style.transform = "";
    });
  };
};