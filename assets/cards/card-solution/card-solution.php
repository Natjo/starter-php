<?php
$args = normalize_args($args ?? null);
$suptitle = $args["suptitle"] ?? "";
$title = $args["title"] ?? "";
$description = $args["description"] ?? "";
$icon = $args["icon"] ?? "";

$classes = component::classes("card-solution", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<div class="<?= $classes ?>" <?= $attributes ?>>
    <div class="card-content">
        <div class="card-header">
            <?php component::eyebrow($suptitle); ?>

            <div class="card-title">
                <?php component::image($icon); ?>

                <?php component::title($args, 3, "title-3"); ?>
            </div>
        </div>

        <?php component::text($args, ""); ?>

        <div class="usage">
            <div class="usage-title"><?php component::icon("star", 10, 10); ?> HOW TO USE</div>

            <?php component::text($args["usage"] ?? ""); ?>
        </div>

        <?php component::icon("blank", 24, 24); ?>
    </div>
</div>