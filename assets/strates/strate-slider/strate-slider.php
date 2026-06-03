<?php
$args = normalize_args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];

?>


<section <?= options("strate strate-slider", $args) ?> data-module="strates/strate-slider" data-context="@visible true">
    <?php component::title($args); ?>

    <?php component::slider($items, "card-news",  null, true, true); ?>
</section>
