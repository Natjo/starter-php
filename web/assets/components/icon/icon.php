<?php
$icon_first = $params[0] ?? null;
$icon_assoc = is_array($params) && $params !== [] && array_keys($params) !== range(0, count($params) - 1);
if ($icon_assoc) {
    $icon_src = $params;
} elseif (is_array($icon_first)) {
    $icon_src = $icon_first;
} else {
    if (empty($icon_first)) return;
    $icon_src = [
        "name" => $icon_first,
        "width" => $params[1] ?? 24,
        "height" => $params[2] ?? 24,
        "classes" => $params[3] ?? null,
        "label" => $params[4] ?? null,
        "attributes" => $params[5] ?? null,
    ];
}
$args = normalize_args([
    "name" => $icon_src["name"] ?? "",
    "width" => $icon_src["width"] ?? 24,
    "height" => $icon_src["height"] ?? 24,
    "url" => THEME_ASSETS,
    "classes" => $icon_src["classes"] ?? null,
    "label" => $icon_src["label"] ?? null,
    "attributes" => $icon_src["attributes"] ?? null,
]);
$name = !empty($args["name"]) ? sanitize_html_class((string) $args["name"]) : "";
$width = !empty($args["width"]) ? max(1, (int) $args["width"]) : 24;
$height = !empty($args["height"]) ? max(1, (int) $args["height"]) : 24;
$url = !empty($args["url"]) ? rtrim((string) $args["url"], "/") . "/" : "";
$classes = component::classes("icon", "icon-" . $name, $args["classes"] ?? "");
$label = isset($args["label"]) && is_scalar($args["label"]) ? trim((string) $args["label"]) : "";
$attributes = component::attributes($args["attributes"] ?? []);

if ($name === "") {
    return;
}
?>

<svg class="<?= $classes ?>" width="<?= esc_attr($width) ?>" height="<?= esc_attr($height) ?>"<?= $label !== "" ? ' role="img" aria-label="' . esc_attr($label) . '"' : ' aria-hidden="true"' ?> focusable="false"<?= $attributes ?>>
    <use href="<?= esc_url($url . 'img/icons.svg#' . $name) ?>"></use>
</svg>
