<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-hybrid_ai", $args) ?>>
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>
</section>