import textAnimated from "../../modules/textAnimated/textAnimated.js";
import Slider from "../../components/slider/slider.js";
export default el => {
  var _slider$add;
  textAnimated(el);
  const sliderEl = el.querySelector(".slider");
  const slider = new Slider(sliderEl);
  slider === null || slider === void 0 || (_slider$add = slider.add) === null || _slider$add === void 0 || _slider$add.call(slider);
  return () => {
    var _slider$remove;
    slider === null || slider === void 0 || (_slider$remove = slider.remove) === null || _slider$remove === void 0 || _slider$remove.call(slider);
  };
};