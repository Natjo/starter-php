<?php
$args = component::args($args ?? null);
$title = !empty($title) ? (string) $title : (!empty($args["title"]) ? (string) $args["title"] : "");
$text = !empty($text) ? (string) $text : (!empty($args["text"]) ? (string) $args["text"] : "");
$link = !empty($link) && is_array($link) ? $link : (!empty($args["link"]) && is_array($args["link"]) ? $args["link"] : []);
$classes = component::classes("component-header", $classes ?? ($args["classes"] ?? ""));
$attributes = component::attributes($attributes ?? ($args["attributes"] ?? []));

if ($title === "" && $text === "" && empty($link)) {
    return;
}
?>

<header class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($title !== "") : ?>
        <?php component::title($title); ?>
    <?php endif; ?>

    <?php if ($text !== "") : ?>
        <?php component::text($text); ?>
    <?php endif; ?>

    <?php if (!empty($link)) : ?>
        <?php component::link($link); ?>
    <?php endif; ?>
</header>
