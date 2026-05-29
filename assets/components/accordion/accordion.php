<?php
$args = component::args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$classes = component::classes("accordion", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

if (empty($items)) return;
?>

<div class="<?= $classes ?>"<?= $attributes ?> data-module="components/accordion" data-context="@visible true">
    <?php foreach ($items as $index => $item) :
        $uniqid = uniqid();
        $title = htmlspecialchars((string) ($item["title"] ?? ""), ENT_QUOTES, "UTF-8");
        $text = (string) ($item["text"] ?? "");

        if ($title === "" && trim($text) === "") {
            continue;
        }
    ?>
        <div class="details">
            <h3 id="summary-<?= $uniqid ?>">
                <button class="summary" type="button" aria-expanded="<?= $index === 0 ? "true" : "false" ?>" aria-controls="panel-<?= $uniqid ?>">
                    <span><?= $title ?></span>
                    <span class="accordion-caret" aria-hidden="true"></span>
                </button>
            </h3>

            <div id="panel-<?= $uniqid ?>" class="details-content" role="region" aria-labelledby="summary-<?= $uniqid ?>" aria-hidden="<?= $index === 0 ? "false" : "true" ?>">
                <div class="text rte">
                    <?= $text ?>
                </div>
            </div>
        </div>
    <?php endforeach ?>
</div>
