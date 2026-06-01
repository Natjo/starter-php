<?php
$args = starter_args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$isList = empty($items) || array_keys($items) === range(0, count($items) - 1);
if (!$isList) {
    $items = [$items];
}
$card = !empty($args["card"]) ? (string) $args["card"] : "card-news";
$navigation  = !empty($args["navigation"]) ? true : false;
$pagination  = !empty($args["pagination"]) ? true : false;
$classes     = component::classes("slider", $args["classes"] ?? "");
$label       = !empty($args["label"]) ? (string) $args["label"] : __("Carrousel", "starterkit");

static $sliderInstance = 0;
$sliderInstance++;
$slider_id   = function_exists("wp_unique_id") ? "slider-" . wp_unique_id() : "slider-" . $sliderInstance;
$status_id   = $slider_id . "-status";

$region_attributes = starter_attributes([
    "class" => $classes,
    "role" => "region",
    "aria-label" => $label,
]);

if (empty($items)) return;
?>

<div <?= $region_attributes ?>>

    <?php if ($navigation) : ?>
        <div class="slider-navigation">
            <button class="slider-btn prev" type="button" aria-label="<?= esc_attr(__("Diapositive précédente", "starterkit")) ?>">
                <span aria-hidden="true">&lt;</span>
            </button>
            <button class="slider-btn next" type="button" aria-label="<?= esc_attr(__("Diapositive suivante", "starterkit")) ?>">
                <span aria-hidden="true">&gt;</span>
            </button>
        </div>
    <?php endif ?>

    <div class="slider-wrapper">
        <ul class="slider-content" role="list">
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

    <div id="<?= esc_attr($status_id) ?>" class="sr-only" aria-live="polite" aria-atomic="true" data-slider-status></div>
</div>
