<?php
$args = normalize_args($args ?? null);
?>

<header class="hero hero-homepage">
    <?php component::title($args,1); ?>

    <?php component::text($args); ?>
</header>
