import { ScrollDriver } from "../../modules/scrollDriver/scrollDriver.js";
export default el => {
  var _ref, _lenis$animatedScroll;
  const canvas = el.querySelector("canvas");
  if (!canvas) return;
  const base = canvas.dataset.framesBase;
  const count = parseInt(canvas.dataset.framesCount, 10) || 0;
  if (!base || count <= 0) return;
  const ctx = canvas.getContext("2d");
  const clamp = value => Math.max(0, Math.min(1, value));
  const pad = n => String(n).padStart(3, "0");
  const images = [];
  let disposed = false;
  let currentPosition = -1;
  const drawCover = (img, alpha = 1) => {
    const width = (img === null || img === void 0 ? void 0 : img.naturalWidth) || (img === null || img === void 0 ? void 0 : img.width);
    const height = (img === null || img === void 0 ? void 0 : img.naturalHeight) || (img === null || img === void 0 ? void 0 : img.height);
    if (!img || !width || !height) return;
    const cw = canvas.width;
    const ch = canvas.height;
    const scale = Math.max(cw / width, ch / height);
    const w = width * scale;
    const h = height * scale;
    ctx.globalAlpha = alpha;
    ctx.drawImage(img, (cw - w) / 2, (ch - h) / 2, w, h);
    ctx.globalAlpha = 1;
  };
  const draw = position => {
    const nextPosition = Math.max(0, Math.min(count - 1, position));
    if (Math.abs(nextPosition - currentPosition) < 0.001) return;
    currentPosition = nextPosition;
    const index = Math.floor(nextPosition);
    const nextIndex = Math.min(count - 1, index + 1);
    const alpha = nextPosition - index;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    drawCover(images[index]);
    drawCover(images[nextIndex], alpha);
  };
  const loadFallbackImage = (index, src) => {
    const img = new Image();
    img.decoding = "async";
    img.onload = () => {
      if (disposed) return;
      images[index] = img;
      if (Math.floor(currentPosition) === index) {
        const position = currentPosition;
        currentPosition = -1;
        draw(position);
      }
    };
    img.src = src;
  };
  const loadFrame = async index => {
    const src = `${base}frame_${pad(index + 1)}.webp`;
    if (!window.createImageBitmap) {
      loadFallbackImage(index, src);
      return;
    }
    try {
      const response = await fetch(src);
      const blob = await response.blob();
      const bitmap = await createImageBitmap(blob);
      if (disposed) {
        var _bitmap$close;
        (_bitmap$close = bitmap.close) === null || _bitmap$close === void 0 || _bitmap$close.call(bitmap);
        return;
      }
      images[index] = bitmap;
      if (Math.floor(currentPosition) === index) {
        const position = currentPosition;
        currentPosition = -1;
        draw(position);
      }
    } catch (_unused) {
      loadFallbackImage(index, src);
    }
  };
  for (let i = 0; i < count; i++) loadFrame(i);
  const resize = () => {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    canvas.width = Math.round(canvas.clientWidth * dpr);
    canvas.height = Math.round(canvas.clientHeight * dpr);
    const position = currentPosition;
    currentPosition = -1;
    draw(Math.max(0, position));
  };
  const driver = new ScrollDriver();
  let target = 0;
  let progress = 0;
  let raf = null;
  const tick = () => {
    progress += (target - progress) * 0.18;
    if (Math.abs(target - progress) < 0.0005) progress = target;
    draw(progress * (count - 1));
    raf = requestAnimationFrame(tick);
  };
  driver.add(el, "bottom-bottom", e => {
    e.timeline(80, 100, val => {
      target = clamp(val);
    });
  });
  driver.enable();
  resize();
  raf = requestAnimationFrame(tick);
  window.addEventListener("resize", resize);
  const lenis = window.lenis;
  const onLenisScroll = instance => driver.onScroll(instance.animatedScroll);
  lenis === null || lenis === void 0 || lenis.on("scroll", onLenisScroll);
  driver.onScroll((_ref = (_lenis$animatedScroll = lenis === null || lenis === void 0 ? void 0 : lenis.animatedScroll) !== null && _lenis$animatedScroll !== void 0 ? _lenis$animatedScroll : window.scrollY) !== null && _ref !== void 0 ? _ref : 0);
  return () => {
    disposed = true;
    lenis === null || lenis === void 0 || lenis.off("scroll", onLenisScroll);
    window.removeEventListener("resize", resize);
    driver.disable();
    if (raf) cancelAnimationFrame(raf);
    images.forEach(img => {
      var _img$close;
      return img === null || img === void 0 || (_img$close = img.close) === null || _img$close === void 0 ? void 0 : _img$close.call(img);
    });
  };
};