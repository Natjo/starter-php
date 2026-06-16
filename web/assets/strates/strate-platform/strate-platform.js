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
  const reapply = () => driver.refresh();
  driver.add(el, "top-bottom", e => {
    e.timeline(0, 100, val => {
      const progress = val / 100;
      const seg = 1 / segments;
      const segIndex = Math.min(segments - 1, Math.floor(progress / seg));
      const t = easeOut(clamp((progress - segIndex * seg) / seg));
      items.forEach((item, index) => {
        let height = 0;
        if (index === segIndex) height = (1 - t) * maxHeights[index];else if (index === segIndex + 1) height = t * maxHeights[index];
        style.var(item.content, `${height}px`, "--height");
        let perone = 0;
        if (index === segIndex) perone = 1 - t;else if (index === segIndex + 1) perone = t;
        style.var(item.content, perone, "--perone");
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
  reapply();
  return () => {
    driver.destroy();
    ro.disconnect();
    style.clear(el);
    items.forEach(item => style.clear(item.content));
  };
};