<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-key_people", $args) ?> data-module="strates/strate-key_people" data-context="@visible true">
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>
</section>
