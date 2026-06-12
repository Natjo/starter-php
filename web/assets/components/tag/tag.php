<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["name" => $args];
if (!is_array($args)) return;
$tag_type = $params[1] ?? "info";
$tag_classes = $params[2] ?? null;
$tag_attributes = $params[3] ?? null;
$tag_allowed = ["info", "btn", "link"];
if (!is_string($tag_type) || !in_array($tag_type, $tag_allowed, true)) {
    if (is_string($tag_type) && trim($tag_type) !== "") {
        $tag_attributes = $tag_classes;
        $tag_classes = $tag_type;
    }
    $tag_type = "info";
}
if (!isset($args["type"]) || $tag_type !== "info") $args["type"] = $tag_type;
if ($tag_classes !== null) $args["classes"] = $tag_classes;
if ($tag_attributes !== null) $args["attributes"] = $tag_attributes;
$args = normalize_args($args);

$payload = $args["args"] ?? $args;
$allowed_types = ["info", "btn", "link"];
$raw_type = isset($args["type"]) && is_string($args["type"]) ? strtolower(trim($args["type"])) : "info";

if (!in_array($raw_type, $allowed_types, true)) {
    $raw_type = "info";
}

$name = "";
$link = null;

if (is_array($payload)) {
    $link = !empty($payload["link"]) && is_array($payload["link"]) ? $payload["link"] : null;

    if (!$link && !empty($payload["url"])) {
        $link = [
            "url" => $payload["url"],
            "title" => $payload["title"] ?? $payload["name"] ?? $payload["tag"] ?? $payload["label"] ?? "",
            "target" => $payload["target"] ?? "",
        ];
    }

    $name = $payload["tag"] ?? $payload["name"] ?? $payload["title"] ?? $payload["label"] ?? $payload["text"] ?? "";
} else {
    $name = $payload;
}

$name = is_scalar($name) ? trim((string) $name) : "";

if ($link) {
    $url = isset($link["url"]) && is_scalar($link["url"]) ? trim((string) $link["url"]) : "";
    $title = isset($link["title"]) && is_scalar($link["title"]) ? trim((string) $link["title"]) : $name;

    if ($url === "" || $title === "") return;

    $target_value = isset($link["target"]) && is_scalar($link["target"]) ? trim((string) $link["target"]) : "";
    $target = $target_value !== "" ? ' target="' . esc_attr($target_value) . '"' : "";

    $raw_attributes = $args["attributes"] ?? [];
    $has_rel = is_array($raw_attributes)
        ? array_key_exists("rel", $raw_attributes)
        : (is_string($raw_attributes) && preg_match('/\brel\s*=/i', $raw_attributes));
    $rel = $target_value === "_blank" && !$has_rel ? ' rel="noopener noreferrer"' : "";

    $raw_type = "link";
    $name = $title;
} else {
    if ($raw_type === "link" || $name === "") {
        return;
    }
}

$classes = component::classes("tag", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$disabled = !empty($args["disabled"]);
$disabled_attribute = $disabled ? ' aria-disabled="true"' : "";
?>

<?php if ($raw_type === "link" && $link) : ?>
    <a href="<?= esc_url($url) ?>" class="<?= $classes ?>"<?= $attributes . $target . $rel . $disabled_attribute ?>>
        <?= esc_html($name) ?>
    </a>
<?php elseif ($raw_type === "btn") : ?>
    <button type="button" class="<?= $classes ?>"<?= $disabled ? " disabled" : "" ?><?= $attributes ?>>
        <?= esc_html($name) ?>
    </button>
<?php else : ?>
    <span class="<?= $classes ?>"<?= $attributes . $disabled_attribute ?>><?= esc_html($name) ?></span>
<?php endif; ?>
