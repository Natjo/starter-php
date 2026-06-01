<?php
$args = starter_args($args ?? null);
$name = !empty($args["name"]) ? sanitize_html_class((string) $args["name"]) : "";
$width = !empty($args["width"]) ? (int) $args["width"] : 24;
$height = !empty($args["height"]) ? (int) $args["height"] : 24;
$url = !empty($args["url"]) ? rtrim((string) $args["url"], "/") . "/" : "";
$classes = component::classes("icon", "icon-" . $name, $args["classes"] ?? "");
$label = isset($args["label"]) && is_scalar($args["label"]) ? trim((string) $args["label"]) : "";

if ($name === "") {
    return;
}
?>

<svg class="<?= $classes ?>" width="<?= esc_attr($width) ?>" height="<?= esc_attr($height) ?>"<?= $label !== "" ? ' role="img" aria-label="' . esc_attr($label) . '"' : ' aria-hidden="true"' ?> focusable="false">
    <use href="<?= esc_attr($url) ?>img/icons.svg#<?= esc_attr($name) ?>"></use>
</svg>
