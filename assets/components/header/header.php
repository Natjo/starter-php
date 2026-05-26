<?php
$args = isset($args) && is_array($args) ? $args : [];
$title = !empty($title) ? (string) $title : (!empty($args["title"]) ? (string) $args["title"] : "");
$text = !empty($text) ? (string) $text : (!empty($args["text"]) ? (string) $args["text"] : "");
$link = !empty($link) && is_array($link) ? $link : (!empty($args["link"]) && is_array($args["link"]) ? $args["link"] : []);
$classes = !empty($classes) ? " " . esc_attr($classes) : (!empty($args["classes"]) ? " " . esc_attr($args["classes"]) : "");
$attributes = !empty($attributes) ? (string) $attributes : (!empty($args["attributes"]) ? (string) $args["attributes"] : "");

if ($title === "" && $text === "" && empty($link)) {
    return;
}
?>

<header class="component-header<?= $classes ?>"<?= $attributes !== "" ? " " . $attributes : "" ?>>
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
