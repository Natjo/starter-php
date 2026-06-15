<?php
$params = isset($params) && is_array($params) ? $params : [];
$args = $params[0] ?? null;
if (is_string($args)) $args = ["name" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $args["classes"] = $params[1];
if (!empty($params[2] ?? null)) $args["icon"] = $params[2];
if (($params[3] ?? null) !== null) $args["attributes"] = $params[3];
$args = normalize_args($args);

$payload = null;
if (!empty($args["button"]) && is_array($args["button"])) {
    $payload = $args["button"];
} elseif (!empty($args["cta"]) && is_array($args["cta"])) {
    $payload = $args["cta"];
}

if ($payload !== null) {
    $args = normalize_args($payload, [
        "classes" => $args["classes"] ?? "",
        "icon" => $args["icon"] ?? [],
        "attributes" => $args["attributes"] ?? [],
    ]);
}

$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : null;
if (!$link && !empty($args["url"])) {
    $link = [
        "url" => $args["url"],
        "title" => $args["title"] ?? $args["name"] ?? "",
        "target" => $args["target"] ?? "",
    ];
}

$name = !empty($args["name"]) ? trim((string) $args["name"]) : "";
$classes = component::classes("btn", $args["classes"] ?? "");
$raw_attributes = $args["attributes"] ?? [];
$attributes = component::attributes($raw_attributes);
$icon = !empty($args["icon"]) && is_array($args["icon"]) ? $args["icon"] : null;

if ($link) {
    $url = trim((string) ($link["url"] ?? ""));
    $title = trim((string) ($link["title"] ?? ""));
    if ($url === "" || $title === "") return;

    $target_value = trim((string) ($link["target"] ?? ""));
    $target = $target_value !== "" ? ' target="' . esc_attr($target_value) . '"' : "";
    $has_rel = is_array($raw_attributes)
        ? array_key_exists("rel", $raw_attributes)
        : (is_string($raw_attributes) && preg_match('/\brel\s*=/i', $raw_attributes));
    $rel = $target_value === "_blank" && !$has_rel ? ' rel="noopener noreferrer"' : "";
?>

    <a href="<?= esc_url($url) ?>" class="<?= $classes ?>"<?= $attributes . $target . $rel ?>><span><?= esc_html($title) ?></span><?php if ($icon) component::icon(...$icon); ?></a>
<?php
    return;
}

if ($name === "") return;

$has_type = is_array($raw_attributes)
    ? array_key_exists("type", $raw_attributes)
    : (is_string($raw_attributes) && preg_match('/\btype\s*=/i', $raw_attributes));
?>

<button<?= $has_type ? "" : ' type="button"' ?> class="<?= $classes ?>"<?= $attributes ?>><span><?= esc_html($name) ?></span><?php if ($icon) component::icon(...$icon); ?></button>
