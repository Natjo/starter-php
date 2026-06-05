<?php
$args = normalize_args($args ?? null);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
?>

<?php if (!empty($items)) : ?>
<section <?= options("strate strate-accordion", $args) ?>>
    <div class="strate-accordion-header">
        <?php component::title($args); ?>

        <?php component::text($args); ?>
    </div>

    <?php component::accordion($items); ?>
</section>
<?php endif; ?>
