<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-ai_news", $args) ?>>
    <div class="strate-content">
       
        <div class='strate-header'> 
            <?php component::eyebrow($args); ?>
            <?php component::title($args, 2, "title-2"); ?>
        </div>

        <?php component::list($args["items"] ?? [], "news"); ?>
    </div>
</section>