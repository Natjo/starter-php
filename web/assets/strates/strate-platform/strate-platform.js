import { ScrollDriver, style } from "../../modules/scrollDriver/scrollDriver.js";
export default el => {
  var _el$querySelectorAll, _ref, _lenis$animatedScroll;
  const platforms = el === null || el === void 0 || (_el$querySelectorAll = el.querySelectorAll) === null || _el$querySelectorAll === void 0 ? void 0 : _el$querySelectorAll.call(el, ".platform");
  if (!(platforms !== null && platforms !== void 0 && platforms.length)) return;
  style.var(el, platforms.length, "--nb-platforms");
  const driver = new ScrollDriver();
  const clamp = value => Math.max(0, Math.min(1, value));
  const count = platforms.length;
  const segments = Math.max(1, count - 1);
  const items = [...platforms].map(platform => ({
    el: platform,
    content: platform.querySelector(".platform-content"),
    inner: platform.querySelector(".platform-content-inner")
  }));
  let maxHeights = items.map(() => 0);
  let lastProgress = 0;
  const measure = () => {
    maxHeights = items.map(item => {
      var _item$inner;
      return ((_item$inner = item.inner) === null || _item$inner === void 0 ? void 0 : _item$inner.offsetHeight) || 0;
    });
  };
  const applyProgress = progress => {
    lastProgress = clamp(progress);
    items.forEach((item, index) => {
      let perone = 0;
      if (count === 1) {
        perone = 1;
      } else if (lastProgress >= 1) {
        perone = index === count - 1 ? 1 : 0;
      } else if (lastProgress <= 0) {
        perone = index === 0 ? 1 : 0;
      } else {
        const raw = lastProgress * segments;
        const current = Math.min(count - 2, Math.floor(raw));
        const t = raw - current;
        if (index === current) perone = 1 - t;else if (index === current + 1) perone = t;
      }
      style.var(item.el, perone, "--perone");
      style.var(item.content, `${perone * maxHeights[index]}px`, "--height");
    });
  };
  driver.add(el, "top-bottom", e => {
    e.timeline(0, 100, val => {
      applyProgress(val);
    });
  });
  driver.enable();
  measure();
  const ro = new ResizeObserver(() => {
    measure();
    applyProgress(lastProgress);
  });
  items.forEach(item => item.inner && ro.observe(item.inner));
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  driver.onScroll((_ref = (_lenis$animatedScroll = lenis === null || lenis === void 0 ? void 0 : lenis.animatedScroll) !== null && _lenis$animatedScroll !== void 0 ? _lenis$animatedScroll : window.scrollY) !== null && _ref !== void 0 ? _ref : 0);
  applyProgress(0);
  return () => {
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    driver.disable();
    ro.disconnect();
    el === null || el === void 0 || el.style.removeProperty("--nb-platforms");
    items.forEach(item => {
      var _item$el, _item$content;
      (_item$el = item.el) === null || _item$el === void 0 || _item$el.style.removeProperty("--perone");
      (_item$content = item.content) === null || _item$content === void 0 || _item$content.style.removeProperty("--height");
    });
  };
};