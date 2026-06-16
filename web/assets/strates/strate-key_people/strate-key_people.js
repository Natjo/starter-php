import { ScrollDriver, style, stagger } from "../../modules/scrollDriver/scrollDriver.js";
import textAnimated from "../../modules/textAnimated/textAnimated.js";
export default el => {
  const destroyText = textAnimated(el);
  const list = el.querySelector('.list');
  const images = el.querySelector('.images');
  const driver = new ScrollDriver();
  driver.add(el, [0, 50], e => {
    e.timeline(0, 100, val => {
      stagger(list, val, {
        softness: 0.2
      });
      stagger(images, val, {
        selector: "img"
      });
    });
  });
  driver.enable();
  return () => {
    destroyText === null || destroyText === void 0 || destroyText();
    driver.destroy();
    style.clear(list);
    style.clear(images);
  };
};