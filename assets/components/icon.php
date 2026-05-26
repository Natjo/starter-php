<?php
$name = !empty($args["name"]) ? (string) $args["name"] : "";
$width = !empty($args["width"]) ? (int) $args["width"] : 24;
$height = !empty($args["height"]) ? (int) $args["height"] : 24;
$url = !empty($args["url"]) ? rtrim((string) $args["url"], "/") . "/" : "";
$classes = !empty($args["classes"]) ? " " . esc_attr($args["classes"]) : "";

if ($name === "") {
    return;
}
?>

<svg class="icon icon-<?= esc_attr($name) ?><?= $classes ?>" width="<?= esc_attr($width) ?>" height="<?= esc_attr($height) ?>" aria-hidden="true" focusable="false">
    <use href="<?= esc_attr($url) ?>img/icons.svg#<?= esc_attr($name) ?>"></use>
</svg>
