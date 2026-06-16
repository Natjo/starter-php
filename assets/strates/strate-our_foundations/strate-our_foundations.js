import textAnimated from "@modules/textAnimated";

export default el => {
    const items = [...el.querySelectorAll(".item")];

    const destroyText = textAnimated(el);

    // images hover
    const images = el.querySelectorAll("picture");
    const hoverController = new AbortController();
    items.forEach((item, index) => {
        item.addEventListener("mouseenter", () => {
            images[index]?.classList.add("active");
        }, { signal: hoverController.signal });
        item.addEventListener("mouseleave", () => {
            images[index]?.classList.remove("active");
        }, { signal: hoverController.signal });
    });


    // restore
    return () => {
        hoverController.abort();
        destroyText?.();
        items.forEach(style.clear);
    };
};
