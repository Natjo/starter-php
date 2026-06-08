<?php
$args = normalize_args($args ?? null);
$name = $args["name"] ?? $args["label"] ?? $args["title"] ?? $args["text"] ?? "";
$name = is_scalar($name) ? trim((string) $name) : "";

if ($name === "") return;

$classes = component::classes("badge", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<span class="<?= $classes ?>"<?= $attributes ?>><?= esc_html($name) ?></span>
