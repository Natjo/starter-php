<?php
$args = normalize_args($args ?? null);
?>

<header class="hero hero-page">
    <div class="hero-content">
        <?php component::title($args, 1, "title-1"); ?>

        <?php component::text($args, "intro"); ?>
    </div>
</header>