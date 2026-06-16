<?php
$args = normalize_args($args ?? null);
$placeholder = $args["placeholder"] ?? null;
?>

<section <?= options("strate strate-key_people", $args) ?> data-module="strates/strate-key_people" data-context="@visible true">

    <div class="strate-header">
        <?php component::eyebrow($args); ?>

        <?php component::title($args, 2, "text-animated"); ?>
        <div class="bg">
            <?php component::icon("pattern", 87, 87, "small"); ?>
            <?php component::icon("pattern", 338, 338); ?>
            <?php component::icon("star-stroke", 38, 38); ?>
        </div>
    </div>

    <div class="images">
        <?php if (!empty($args["placeholder"])) : ?>
            <?php component::image($args["placeholder"], "full", "placeholder"); ?>
        <?php endif; ?>
        <?php foreach ($args["items"] ?? [] as $item) : ?>
            <?php if (!empty($item["image"])) : ?>
                <?php component::image($item); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php component::list($args, "card-people"); ?>

</section>