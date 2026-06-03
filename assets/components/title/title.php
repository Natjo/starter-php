<?php
$args = normalize_args($args ?? null);
$title = $args["title"] ?? $args["titre"] ?? $args["heading"] ?? $args["headline"] ?? "";
$title = is_scalar($title) ? (string) $title : "";

if ($title === "") return;


$level = max(1, min(6, (int) ($args["hx"] ?? 2)));
$hx = "h" . $level;
$classes = component::classes("title", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$html = array_key_exists("html", $args) ? (bool) $args["html"] : true;
$content = $html ? starter_kses_post($title) : esc_html($title);
?>

<<?= $hx ?> class="<?= $classes ?>"<?= $attributes ?>><?= $content ?></<?= $hx ?>>
