<?php
$args = $params[0] ?? null;
if (is_string($args)) $args = ["title" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $args["classes"] = $params[1];
if (($params[2] ?? null) !== null) $args["attributes"] = $params[2];
$args = normalize_args($args);
$title = $args["title"] ?? $args["titre"] ?? $args["heading"] ?? $args["headline"] ?? "";
$title = is_scalar($title) ? trim((string) $title) : "";
$text = $args["text"] ?? $args["intro"] ?? $args["content"] ?? $args["description"] ?? "";
$text = is_scalar($text) ? trim((string) $text) : "";
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : null;
$link = $link ?: (!empty($args["cta"]) && is_array($args["cta"]) ? $args["cta"] : null);
$link = $link ?: (!empty($args["button"]) && is_array($args["button"]) ? $args["button"] : null);
$classes = component::classes("component-header", $classes ?? ($args["classes"] ?? ""));
$attributes = component::attributes($attributes ?? ($args["attributes"] ?? []));

if ($title === "" && $text === "" && empty($link)) {
    return;
}
?>

<header class="<?= $classes ?>"<?= $attributes ?>>

    <?php component::title($title); ?>

    <?php component::text($text); ?>

    <?php component::link($link); ?>
</header>
