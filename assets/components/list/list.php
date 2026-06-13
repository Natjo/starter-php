<?php
$list_items = $params[0] ?? null;
if (is_array($list_items) && isset($list_items["items"]) && is_array($list_items["items"])) {
    $list_items = $list_items["items"];
}
if (empty($list_items)) return;
$list_card = $params[1] ?? "news";
if ($list_card === "news") $list_card = "card-news";
$args = normalize_args([
    "items" => $list_items,
    "card" => $list_card,
    "classes" => $params[2] ?? null,
]);
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


<ul class="<?= $classes ?>" role="list">
    <?php foreach ($items as $item) : ?>
        <li class="item">
            <?php card($card, is_array($item) ? $item : []); ?>
        </li>
    <?php endforeach; ?>
</ul>
