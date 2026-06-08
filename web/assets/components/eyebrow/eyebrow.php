<?php
$args = normalize_args($args ?? null);
$text = $args["eyebrow"]
    ?? $args["suptitle"]
    ?? $args["sup_title"]
    ?? $args["tag"]
    ?? $args["category"]
    ?? $args["categorie"]
    ?? $args["text"]
    ?? $args["label"]
    ?? "";
$text = is_scalar($text) ? trim((string) $text) : "";
$element = $args["element"] ?? $args["html_tag"] ?? "p";
$element = is_scalar($element) ? strtolower(trim((string) $element)) : "p";
$element = in_array($element, ["p", "span", "div"], true) ? $element : "p";
$classes = component::classes("eyebrow", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

if ($text === "") return;
?>

<<?= $element ?> class="<?= $classes ?>"<?= $attributes ?>><?= esc_html($text) ?></<?= $element ?>>
