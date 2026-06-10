<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-platform", $args) ?> data-module="strates/strate-platform" data-context="@visible true">
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>

    </div>
</section>