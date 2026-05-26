<?php
$args = isset($args) && is_array($args) ? $args : [];
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$card = !empty($args["card"]) ? (string) $args["card"] : "card-news";
$classes = !empty($args["classes"]) ? " " . esc_attr($args["classes"]) : "";

$isList = empty($items) || array_keys($items) === range(0, count($items) - 1);
if (!$isList) {
    $items = [$items];
}

if (empty($items)) {
    return;
}
?>

<div class="list<?= $classes ?>">
    <ul class="list__items" role="list">
        <?php foreach ($items as $item) : ?>
            <li class="list__item">
                <?php component::card($card, is_array($item) ? $item : []); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
