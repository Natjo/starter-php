<?php
$args = component::args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$isList = empty($items) || array_keys($items) === range(0, count($items) - 1);
if (!$isList) {
    $items = [$items];
}
$card = !empty($args["card"]) ? (string) $args["card"] : "card-news";
$navigation  = !empty($args["navigation"]) ? true : false;
$pagination  = !empty($args["pagination"]) ? true : false;
$classes     = component::classes("slider", $args["classes"] ?? "");
$label       = !empty($args["label"]) ? ' aria-label="' . htmlspecialchars((string) $args["label"], ENT_QUOTES, "UTF-8") . '"' : "";
$slider_id   = function_exists("wp_unique_id") ? "slider-" . wp_unique_id() : "slider-" . uniqid();
$status_id   = $slider_id . "-status";

if (empty($items)) return;
?>

<div class="<?= $classes ?>" role="region"<?= $label ?>>

    <?php if ($navigation) : ?>
        <div class="slider-navigation" aria-hidden="true">
            <button class="slider-btn prev" type="button" tabindex="-1">
                <span aria-hidden="true">&lt;</span>
            </button>
            <button class="slider-btn next" type="button" tabindex="-1">
                <span aria-hidden="true">&gt;</span>
            </button>
        </div>
    <?php endif ?>

    <div class="slider-wrapper">
        <ul class="slider-content" role="list" data-lenis-prevent-wheel>
            <?php foreach ($items as $item) : ?>
                <li class="item">
                    <?php component::card($card, $item) ?>
                </li>
            <?php endforeach ?>
        </ul>
    </div>

    <?php if ($pagination) : ?>
        <nav class="slider-pagination" aria-label="Navigation du carrousel" data-slider-pagination></nav>
    <?php endif ?>

    <div id="<?= htmlspecialchars($status_id, ENT_QUOTES, "UTF-8") ?>" class="sr-only" aria-live="polite" aria-atomic="true" data-slider-status></div>
</div>
