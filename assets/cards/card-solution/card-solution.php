<?php
$args = normalize_args($args ?? null);
$suptitle = $args["suptitle"] ?? "";
$title = $args["title"] ?? "";
$description = $args["description"] ?? "";
$icon = $args["icon"] ?? "";

$classes = component::classes("card-ui", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<div class="<?= $classes ?>" <?= $attributes ?>>
    <div class="card-content"> 
        <?php component::eyebrow($suptitle); ?>
        <?php component::icon($icon, 40, 40); ?>
        <?php component::title($args, 3, "title-3"); ?>
        <?php component::text($args, ""); ?>
    </div>
</div>