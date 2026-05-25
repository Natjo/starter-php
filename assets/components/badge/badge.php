<?php
$args = isset($args) && is_array($args) ? $args : [];
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
$classes = !empty($args["classes"]) ? " " . (string) $args["classes"] : "";
$attributes = !empty($args["attributes"]) ? (string) $args["attributes"] : "";
?>

<div class="badge<?= $escAttr($classes) ?>" <?= $attributes ?>>
    <?= $escHtml($name) ?>
</div>
