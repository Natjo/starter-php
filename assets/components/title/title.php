<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["title" => $args];
if (!is_array($args)) return;
$hx = $params[1] ?? 2;
if ($hx !== null) $args["hx"] = $hx;
if (($params[2] ?? null) !== null) $args["classes"] = $params[2];
if (($params[3] ?? null) !== null) $args["attributes"] = $params[3];
$args = normalize_args($args);
$title = $args["title"] ?? $args["titre"] ?? $args["heading"] ?? $args["headline"] ?? "";
$title = is_scalar($title) ? (string) $title : "";

if ($title === "") return;


$level = max(1, min(6, (int) ($args["hx"] ?? 2)));
$hx = "h" . $level;
$classes = component::classes("title", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$html = array_key_exists("html", $args) ? (bool) $args["html"] : true;
$content = $html ? wp_kses_post($title) : esc_html($title);
?>

<<?= $hx ?> class="<?= $classes ?>"<?= $attributes ?>><?= $content ?></<?= $hx ?>>
