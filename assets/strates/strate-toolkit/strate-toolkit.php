<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-toolkit", $args) ?> data-module="strates/strate-toolkit" data-context="@visible true">
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>
</section>