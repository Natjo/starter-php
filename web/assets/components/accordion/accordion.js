export default el => {
  const multiple = el.dataset.multiple === "true";
  const items = [...el.querySelectorAll(".details")].map(detail => {
    const button = detail.querySelector(".summary");
    const panel = button ? document.getElementById(button.getAttribute("aria-controls")) : null;
    return button && panel ? {
      button,
      panel,
      expanded: button.getAttribute("aria-expanded") === "true"
    } : null;
  }).filter(Boolean);
  if (!items.length) return;
  const setExpanded = (item, expanded) => {
    if (item.expanded === expanded) return;
    item.expanded = expanded;
    item.button.setAttribute("aria-expanded", expanded);
    item.panel.setAttribute("aria-hidden", !expanded);
  };
  const toggle = selected => {
    const shouldOpen = !selected.expanded;
    if (multiple) {
      setExpanded(selected, shouldOpen);
      return;
    }
    items.forEach(item => {
      setExpanded(item, item === selected ? shouldOpen : false);
    });
  };
  items.forEach(item => {
    item.button.addEventListener("click", () => toggle(item));
  });
};