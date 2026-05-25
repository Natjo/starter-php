
export default (el) => {
    const details = el.querySelectorAll(".details");

    const onclick = selected => {
        details.forEach(detail => {
            const summary = detail.querySelector(".summary");
            const content = detail.querySelector(`#${summary.getAttribute("aria-controls")}`);
            let expanded = summary.getAttribute("aria-expanded") === "false" ? true : false;
            summary.setAttribute("aria-expanded", selected === summary ? expanded : false);
            content.setAttribute("aria-hidden", selected === summary ? !expanded : true);
        })
    }

    details.forEach(detail => {
        const summary = detail.querySelector(".summary");
        summary.onclick = () => onclick(summary)
    })
};
