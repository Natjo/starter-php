import textAnimated from "../../modules/textAnimated/textAnimated.js";
export default el => {
  const items = [...el.querySelectorAll(".item")];
  const destroyText = textAnimated(el);
  const images = el.querySelectorAll("picture");
  const hoverController = new AbortController();
  items.forEach((item, index) => {
    item.addEventListener("mouseenter", () => {
      var _images$index;
      (_images$index = images[index]) === null || _images$index === void 0 || _images$index.classList.add("active");
    }, {
      signal: hoverController.signal
    });
    item.addEventListener("mouseleave", () => {
      var _images$index2;
      (_images$index2 = images[index]) === null || _images$index2 === void 0 || _images$index2.classList.remove("active");
    }, {
      signal: hoverController.signal
    });
  });
  return () => {
    hoverController.abort();
    destroyText === null || destroyText === void 0 || destroyText();
    items.forEach(style.clear);
  };
};