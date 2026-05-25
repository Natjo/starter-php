<?php
$args = isset($args) && is_array($args) ? $args : [];
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];

?>


<section <?= options("strate strate-slider", $args) ?> data-module="strates/strate-slider/strate-slider">
    <?php component::title($args); ?>

    <?php component::slider($items, "card-news",  null, true, true); ?>
</section>

