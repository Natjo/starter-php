<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["eyebrow" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $extra_classes = $params[1];
if (($params[2] ?? null) !== null) $args["attributes"] = $params[2];
$args = normalize_args($args);
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
$classes = component::classes("eyebrow", $args["classes"] ?? "", $extra_classes ?? null);
$attributes = component::attributes($args["attributes"] ?? []);

if ($text === "") return;
?>

<<?= $element ?> class="<?= $classes ?>"<?= $attributes ?>><?php component::icon("star",15,15)?><?= wp_kses_post($text) ?></<?= $element ?>>
