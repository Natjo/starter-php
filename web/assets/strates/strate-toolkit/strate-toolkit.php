<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-toolkit", $args) ?> data-module="strates/strate-toolkit" data-context="@visible true">
    <div class="strate-content">
        <div class='strate-header'>
            <?php component::eyebrow($args); ?>

            <?php component::title($args, 2, "title-2 text-animated"); ?>
        </div>
        
        <?php component::list($args["items"] ?? [], "solution"); ?>
    </div>
</section>