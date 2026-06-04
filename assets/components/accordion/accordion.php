<?php
$args = normalize_args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$items = array_values(array_filter(array_map(static function ($item) {
    if (!is_array($item)) return null;

    $title = isset($item["title"]) && is_scalar($item["title"]) ? trim((string) $item["title"]) : "";
    $text = isset($item["text"]) && is_scalar($item["text"]) ? trim((string) $item["text"]) : "";

    return $title !== "" && $text !== "" ? ["title" => $title, "text" => $text] : null;
}, $items)));
$classes = component::classes("accordion", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$multiple = !empty($args["multiple"]);
$uid = uniqid("accordion-");

if (empty($items)) return;
?>

<div class="<?= $classes ?>"<?= $attributes ?> data-module="components/accordion" data-context="@visible true" data-multiple="<?= $multiple ? "true" : "false" ?>">
    <?php foreach ($items as $index => $item) :
        $title = $item["title"];
        $text = $item["text"];
        $summary_id = $uid . "-summary-" . $index;
        $panel_id = $uid . "-panel-" . $index;
        $is_open = $multiple || $index === 0;
    ?>
        <div class="details">
            <h3 id="<?= esc_attr($summary_id) ?>">
                <button class="summary" type="button" aria-expanded="<?= $is_open ? "true" : "false" ?>" aria-controls="<?= esc_attr($panel_id) ?>">
                    <span><?= esc_html($title) ?></span>
                    <span class="accordion-caret" aria-hidden="true"></span>
                </button>
            </h3>

            <div id="<?= esc_attr($panel_id) ?>" class="details-content" aria-labelledby="<?= esc_attr($summary_id) ?>" aria-hidden="<?= $is_open ? "false" : "true" ?>">
                <div class="text rte">
                    <?= wp_kses_post($text) ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>
