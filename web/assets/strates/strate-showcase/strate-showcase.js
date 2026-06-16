import { ScrollDriver, style } from "../../modules/scrollDriver/scrollDriver.js";
export default el => {
  var _window$matchMedia, _window;
  const reduceMotion = (_window$matchMedia = (_window = window).matchMedia) === null || _window$matchMedia === void 0 ? void 0 : _window$matchMedia.call(_window, "(prefers-reduced-motion: reduce)").matches;
  const columns = [...el.querySelectorAll(".col")];
  const speeds = [2.6, 3.6, 3.1, 2.75];
  const amplitude = 500;
  if (reduceMotion || !columns.length) return;
  const driver = new ScrollDriver();
  driver.add(el, [100, 0], e => {
    e.timeline(0, 100, val => {
      const centered = val / 100 - 0.5;
      columns.forEach((col, index) => {
        var _speeds$index;
        const speed = (_speeds$index = speeds[index]) !== null && _speeds$index !== void 0 ? _speeds$index : 1;
        style.translate(col, 0, -centered * amplitude * speed, 0);
      });
    });
  });
  driver.enable();
  return () => {
    driver.destroy();
    columns.forEach(style.clear);
  };
};