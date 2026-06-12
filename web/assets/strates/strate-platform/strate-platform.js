import { ScrollDriver, style, stagger } from "../../modules/scrollDriver/scrollDriver.js";
export default el => {
  var _el$querySelectorAll;
  const platforms = el === null || el === void 0 || (_el$querySelectorAll = el.querySelectorAll) === null || _el$querySelectorAll === void 0 ? void 0 : _el$querySelectorAll.call(el, ".platform");
  if (!(platforms !== null && platforms !== void 0 && platforms.length)) return;
  style.var(el, platforms.length, "--nb-platforms");
  const driver = new ScrollDriver();
  const easeOut = t => 1 - Math.pow(1 - t, 3);
  const clamp = value => Math.max(0, Math.min(1, value));
  const segments = Math.max(1, platforms.length - 1);
  const items = [...platforms].map(platform => ({
    content: platform.querySelector(".platform-content"),
    inner: platform.querySelector(".platform-content-inner")
  }));
  let maxHeights = items.map(() => 0);
  const measure = () => {
    maxHeights = items.map(item => {
      var _item$inner;
      return ((_item$inner = item.inner) === null || _item$inner === void 0 ? void 0 : _item$inner.offsetHeight) || 0;
    });
  };
  const reapply = () => {
    var _ref, _window$lenis$animate, _window$lenis;
    return driver.onScroll((_ref = (_window$lenis$animate = (_window$lenis = window.lenis) === null || _window$lenis === void 0 ? void 0 : _window$lenis.animatedScroll) !== null && _window$lenis$animate !== void 0 ? _window$lenis$animate : window.scrollY) !== null && _ref !== void 0 ? _ref : 0);
  };
  driver.add(el, "top-bottom", e => {
    e.timeline(0, 100, val => {
      const seg = 1 / segments;
      const segIndex = Math.min(segments - 1, Math.floor(val / seg));
      const t = easeOut(clamp((val - segIndex * seg) / seg));
      items.forEach((item, index) => {
        let height = 0;
        if (index === segIndex) height = (1 - t) * maxHeights[index];else if (index === segIndex + 1) height = t * maxHeights[index];
        style.var(item.content, `${height}px`, "--height");
      });
    });
  });
  driver.enable();
  measure();
  const ro = new ResizeObserver(() => {
    measure();
    reapply();
  });
  items.forEach(item => item.inner && ro.observe(item.inner));
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  reapply();
  return () => {
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    driver.disable();
    ro.disconnect();
    items.forEach(item => {
      var _item$content;
      return (_item$content = item.content) === null || _item$content === void 0 ? void 0 : _item$content.style.removeProperty("--height");
    });
  };
};