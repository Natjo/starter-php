<?php
$args = isset($args) && is_array($args) ? $args : [];
?>

<section <?= options("strate strate-text", $args) ?>>
    <?php component::title($args); ?>

    <?php component::text($args); ?>

    <?php component::link($args); ?>
</section>