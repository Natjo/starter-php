<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-ai_news", $args) ?>>
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>
</section>