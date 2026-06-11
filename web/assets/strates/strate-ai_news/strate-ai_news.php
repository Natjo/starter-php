<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-ai_news", $args) ?>>
    <div class="strate-content">
        <?php component::eyebrow($args); ?>

        <?php component::title($args, 2, "title-2"); ?>

        <?php component::list($args["items"] ?? [], "news"); ?>
    </div>
</section>