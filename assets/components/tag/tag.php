<?php
$payload_args = $args["args"] ?? null;
$type = $args["type"] ?? "info";
$classes = $args["classes"] ?? null;
$attributes = $args["attributes"] ?? null;

$allowed_types = ["info", "btn", "link"];
$raw_type = is_string($type) ? strtolower(trim((string) $type)) : "";

// Back-compat: older usage could pass classes as the 2nd argument
// (because `$type` was ignored previously).
if ($raw_type === "" || !in_array($raw_type, $allowed_types, true)) {
    if (is_string($type) && trim((string) $type) !== "") {
        $attributes = $classes;
        $classes = $type;
    }
    $raw_type = "info";
}

$name = "";
$link = null;

if ($raw_type === "link") {
    if (!is_array($payload_args)) return;
    $title = isset($payload_args["title"]) ? trim((string) $payload_args["title"]) : "";
    $url = isset($payload_args["url"]) ? trim((string) $payload_args["url"]) : "";
    $target = isset($payload_args["target"]) ? (string) $payload_args["target"] : "";
    if ($title === "" || $url === "") return;
    $link = [
        "title" => $title,
        "url" => $url,
        "target" => $target,
    ];
    $name = $title;
} else {
    if (is_array($payload_args)) {
        if (isset($payload_args["tag"])) $name = (string) $payload_args["tag"];
        elseif (isset($payload_args["name"])) $name = (string) $payload_args["name"];
        elseif (isset($payload_args["title"])) $name = (string) $payload_args["title"];
    } else {
        $name = (string) $payload_args;
    }
    $name = trim($name);
    if ($name === "") return;
}

$classes = !empty($classes) ? " " . (string) $classes : "";
$attributes = !empty($attributes) ? (string) $attributes : "";
$target = "";
if ($raw_type === "link" && $link) {
    $target = !empty($link["target"]) && $link["target"] !== "" ? ' target="_blank"' : "";
}
?>

<?php if ($raw_type === "link" && $link) : ?>
    <a href="<?= esc_url($link["url"] ?? "") ?>" class="tag<?= esc_attr($classes) ?>" <?= $attributes . $target ?>>
        <?= esc_html($link["title"] ?? "") ?>
    </a>
<?php elseif ($raw_type === "btn") : ?>
    <button type="button" class="tag<?= esc_attr($classes) ?>" <?= $attributes ?>>
        <?= esc_html($name) ?>
    </button>
<?php else : ?>
    <div class="tag<?= esc_attr($classes) ?>" <?= $attributes ?>>
        <?= esc_html($name) ?>
    </div>
<?php endif; ?>