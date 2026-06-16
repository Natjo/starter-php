<?php
$args = normalize_args($args ?? null);
?>

<section <?= options("strate strate-toolkit", $args) ?> data-module="strates/strate-toolkit">

    <div class='strate-header'>
        <?php component::eyebrow($args); ?>

        <?php component::title($args, 2, "title-2 text-animated"); ?>
    </div>

    <?php component::slider($args["items"], "solution", 
    ["pagination"=> false, "navigation" => false, "timeline" => true]); ?>

</section>
