<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["name" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $args["classes"] = $params[1];
if (($params[2] ?? null) !== null) $args["attributes"] = $params[2];
$args = normalize_args($args);
$name = $args["name"] ?? $args["label"] ?? $args["title"] ?? $args["text"] ?? "";
$name = is_scalar($name) ? trim((string) $name) : "";

if ($name === "") return;

$classes = component::classes("badge", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<span class="<?= $classes ?>"<?= $attributes ?>><?= esc_html($name) ?></span>
