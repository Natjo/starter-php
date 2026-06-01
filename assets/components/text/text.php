<?php
$args = starter_args($args ?? null);
$text = $args["text"] ?? $args["intro"] ?? $args["legend"] ?? $args["content"] ?? $args["description"] ?? "";
$text = is_scalar($text) ? trim((string) $text) : "";
$classes = component::classes("text", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$html = array_key_exists("html", $args) ? (bool) $args["html"] : true;
$content = $html ? starter_kses_post($text) : esc_html($text);

if ($text === "") return;
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?= $content ?>
</div>
