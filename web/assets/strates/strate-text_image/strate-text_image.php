<?php
$args = normalize_args($args ?? null);
$reverse = !empty($args["is_reverse"]) ? " reverse" : "";
?>

<section <?= options("strate strate-text_image" . $reverse, $args) ?>>
    <div class="strate-content">
        <?php component::title($args); ?>

        <?php component::text($args); ?>

        <?php component::btn($args); ?>

    </div>

    <?php component::picture($args); ?>
</section>
