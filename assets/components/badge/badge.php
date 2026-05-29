<?php
$args = component::args($args ?? null);
$escAttr = function ($value) {
    if (function_exists("esc_attr")) {
        return esc_attr($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};
$escHtml = function ($value) {
    if (function_exists("esc_html")) {
        return esc_html($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};

$name = !empty($args["name"]) ? trim((string) $args["name"]) : "";
if ($name === "") return;
$classes = component::classes("badge", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?= $escHtml($name) ?>
</div>
