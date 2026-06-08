<?php
$args = normalize_args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$card = !empty($args["card"]) ? (string) $args["card"] : "card-news";
$classes = component::classes("list", $args["classes"] ?? "");

$isList = empty($items) || array_keys($items) === range(0, count($items) - 1);
if (!$isList) {
    $items = [$items];
}

if (empty($items)) {
    return;
}
?>

<div class="<?= $classes ?>">
    <ul class="list__items" role="list">
        <?php foreach ($items as $item) : ?>
            <li class="list__item">
                <?php card($card, is_array($item) ? $item : []); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
