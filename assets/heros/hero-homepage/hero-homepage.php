<?php
$args = isset($args) && is_array($args) ? $args : [];
?>

<header class="hero hero-homepage">
    <?php component::title($args,1); ?>

    <?php component::text($args); ?>
</header>