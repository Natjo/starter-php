<?php
$args = starter_args($args ?? null);
?>

<section <?= options("strate strate-text", $args) ?>>
    <?php component::title($args); ?>

    <?php component::text($args); ?>

    <?php component::link($args); ?>
</section>
