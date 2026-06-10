<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-learn", $args) ?> data-module="strates/strate-learn" data-context="@visible true">
    <?php component::title($args); ?>
</section>
