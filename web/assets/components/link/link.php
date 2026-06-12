<?php
$link_input = $params[0] ?? null;
if (empty($link_input)) return;
if (is_string($link_input)) $link_input = ["link" => ["url" => $link_input, "title" => $link_input]];
if (!is_array($link_input)) return;
if (!empty($link_input["button"]) && is_array($link_input["button"])) $link_input = $link_input["button"];
$link_data = !empty($link_input["link"]) && is_array($link_input["link"]) ? $link_input["link"] : $link_input;
if (empty($link_data["title"])) return;
$args = normalize_args([
    "link" => $link_data,
    "classes" => ($params[1] ?? null) ?? ($link_input["classes"] ?? ""),
    "icon" => ($params[2] ?? null) ?? ($link_input["icon"] ?? null),
    "attributes" => ($params[3] ?? null) ?? ($link_input["attributes"] ?? ""),
]);
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : $args;
$url = isset($link["url"]) && is_scalar($link["url"]) ? trim((string) $link["url"]) : "";
$title = isset($link["title"]) && is_scalar($link["title"]) ? trim((string) $link["title"]) : "";

if ($url === "" || $title === "") return;

$target_value = isset($link["target"]) && is_scalar($link["target"]) ? trim((string) $link["target"]) : "";
$target = $target_value !== "" ? ' target="' . esc_attr($target_value) . '"' : "";
$raw_attributes = $args["attributes"] ?? [];
$has_rel = is_array($raw_attributes)
    ? array_key_exists("rel", $raw_attributes)
    : (is_string($raw_attributes) && preg_match('/\brel\s*=/i', $raw_attributes));
$rel = $target_value === "_blank" && !$has_rel ? ' rel="noopener noreferrer"' : "";
$classes = component::classes("link", $args["classes"] ?? "");
$attributes = component::attributes($raw_attributes);

if (!empty($args["icon"]) && is_array($args["icon"])) {
    $icon = $args["icon"];
    $name = (string) ($icon[0] ?? $icon["name"] ?? "");
    $width = isset($icon[1]) ? (float) $icon[1] : (float) ($icon["width"] ?? 20);
    $height = isset($icon[2]) ? (float) $icon[2] : (float) ($icon["height"] ?? 20);
    if ($width <= 0) $width = 20;
    if ($height <= 0) $height = 20;
} else {
    $icon = null;
}
?>

<a href="<?= esc_url($url) ?>" class="<?= $classes ?>"<?= $attributes . $target . $rel ?>><span><?= esc_html($title) ?></span><?php if ($icon) component::icon($name, $width, $height); ?></a>
