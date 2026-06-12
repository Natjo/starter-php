<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-hybrid_ai", $args) ?>>
    <div class="strate-content">
        <?php component::icon("hybrid_ai_light", 251, 57); ?>

        <?php component::text($args); ?>
        
        <?php if (!empty($args["subtitle"])) : ?>
            <div class="subtitle">
                <?= $args["subtitle"] ?>
            </div>
        <?php endif; ?>
    </div>

    <?php component::video(THEME_UPLOADS . "hybrid_ai.mp4", "", null, true, true); ?>
</section>