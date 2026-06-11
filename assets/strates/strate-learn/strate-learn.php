<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-learn", $args) ?> data-module="strates/strate-learn" data-context="@visible true">
    
    <?php component::eyebrow($args); ?>
    <?php component::title($args, 2, "title-2"); ?>
    <?php component::list($args["items"] ?? [], "learn"); ?>
</section>