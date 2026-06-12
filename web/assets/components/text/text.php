<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["text" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $args["classes"] = $params[1];
if (($params[2] ?? null) !== null) $args["attributes"] = $params[2];
$args = normalize_args($args);
$text = $args["text"] ?? $args["intro"] ?? $args["legend"] ?? $args["content"] ?? $args["description"] ?? "";
$text = is_scalar($text) ? trim((string) $text) : "";
$classes = component::classes("text", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$html = array_key_exists("html", $args) ? (bool) $args["html"] : true;
$content = $html ? wp_kses_post($text) : esc_html($text);

if ($text === "") return;
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?= $content ?>
</div>
