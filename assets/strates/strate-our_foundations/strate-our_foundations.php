<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-our_foundations", $args) ?> data-module="strates/strate-our_foundations" data-context="@visible true">
    <div class="strate-content">
        <?php component::eyebrow($args); ?>
        <?php component::title($args, 2, "title-4 title-animate"); ?>
        <?php component::list($args["items"] ?? [], "foundation"); ?>
    </div>

    <div class="images">
        <?php foreach ($args["items"] ?? [] as $item) : ?>
            <?php if (!empty($item["image"])) : ?>
                <?php component::picture($item["image"]); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>