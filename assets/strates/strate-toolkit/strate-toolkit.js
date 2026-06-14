import textAnimated from "@modules/textAnimated";
import Slider from "@components/slider";

export default el => {
    textAnimated(el);

    const sliderEl = el.querySelector(".slider"); 
    const slider = new Slider(sliderEl);
    slider?.add?.();

    return () => {
        slider?.remove?.();
    };
};
