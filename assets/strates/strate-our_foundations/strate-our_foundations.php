<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-our_foundations", $args) ?> data-module="strates/strate-our_foundations" data-context="@visible true">
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>
</section>
