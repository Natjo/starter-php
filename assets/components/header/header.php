<?php
$args = starter_args($args ?? null);
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
