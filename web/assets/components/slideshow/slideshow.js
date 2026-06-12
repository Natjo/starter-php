export default el => {
  const slides = [...el.querySelectorAll(".slideshow-slide")];
  if (slides.length < 2) return;
  const N = slides.length;
  const INTERVAL = parseInt(el.dataset.interval, 10) || 3000;
  const DURATION = parseInt(el.dataset.duration, 10) || 700;
  const OFFSET = 20;
  el.style.setProperty("--slideshow-duration", `${DURATION}ms`);
  let current = 0;
  let stopped = false;
  let timerId = null;
  let finalizeId = null;
  const loadSlide = index => new Promise(resolve => {
    const slide = slides[index];
    slide.querySelectorAll("[data-srcset]").forEach(node => {
      node.setAttribute("srcset", node.dataset.srcset);
      delete node.dataset.srcset;
    });
    slide.querySelectorAll("[data-src]").forEach(node => {
      node.setAttribute("src", node.dataset.src);
      delete node.dataset.src;
    });
    const img = slide.querySelector("img");
    if (!img || img.complete && img.naturalWidth > 0) {
      resolve();
      return;
    }
    const done = () => {
      img.removeEventListener("load", done);
      img.removeEventListener("error", done);
      resolve();
    };
    img.addEventListener("load", done);
    img.addEventListener("error", done);
  });
  const wipeTo = upcoming => new Promise(resolve => {
    slides.forEach(slide => slide.style.zIndex = "0");
    slides[upcoming].style.zIndex = "1";
    slides[current].style.zIndex = "2";
    const incoming = slides[upcoming];
    incoming.style.setProperty("--width", "100%");
    incoming.style.transition = "none";
    incoming.style.transform = `translateX(${OFFSET}px)`;
    void incoming.offsetWidth;
    incoming.style.transition = "";
    slides[current].style.setProperty("--width", "0%");
    incoming.style.transform = "translateX(0px)";
    finalizeId = window.setTimeout(() => {
      slides[current].style.zIndex = "0";
      slides[current].style.setProperty("--width", "100%");
      slides[current].style.transform = "";
      current = upcoming;
      slides[current].style.zIndex = "2";
      resolve();
    }, DURATION);
  });
  const tick = async () => {
    if (stopped) return;
    const upcoming = (current + 1) % N;
    await loadSlide(upcoming);
    if (stopped) return;
    await wipeTo(upcoming);
    if (stopped) return;
    loadSlide((current + 1) % N);
    timerId = window.setTimeout(tick, INTERVAL);
  };
  slides.forEach(slide => slide.style.zIndex = "0");
  slides[current].style.zIndex = "2";
  loadSlide((current + 1) % N);
  timerId = window.setTimeout(tick, INTERVAL);
  return () => {
    stopped = true;
    window.clearTimeout(timerId);
    window.clearTimeout(finalizeId);
    slides.forEach(slide => {
      slide.style.removeProperty("z-index");
      slide.style.removeProperty("--width");
      slide.style.removeProperty("transform");
      slide.style.removeProperty("transition");
    });
  };
};