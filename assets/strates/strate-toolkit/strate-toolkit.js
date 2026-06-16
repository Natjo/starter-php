import textAnimated from "@modules/textAnimated";
import Slider from "@components/slider";

export default el => {
    const destroyText = textAnimated(el);

    const sliderEl = el.querySelector(".slider"); 
    const slider = new Slider(sliderEl);
    slider?.add?.();

    return () => {
        destroyText?.();
        slider?.remove?.();
    };
};
