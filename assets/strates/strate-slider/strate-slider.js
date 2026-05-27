import Slider from "@components/slider";

export default el => {
    const slider = el.querySelector(".slider");
    if (!slider) return;

    const myslider = new Slider(slider);
    myslider.add();
}
