<?php
$args = normalize_args($args ?? null);

?>

<header class="hero hero-homepage">
    <div class="hero-content">
        <?php component::eyebrow($args); ?>
        <?php component::title($args, 1, "title-1"); ?>
        <?php component::text($args, "intro"); ?>
    </div>

    <?php component::picture([
        ...$args,
        "lazy" => false,
        "preload" => true,
    ]); ?>

    <?php component::icon("hybrid_ai", 240, 55, "hybrid_ai") ?>
</header>