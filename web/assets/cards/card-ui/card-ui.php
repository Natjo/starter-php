<?php
$args = normalize_args($args ?? null);
$url = is_scalar($args["url"] ?? null) ? trim((string) $args["url"]) : "";
$classes = component::classes("card-ui", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<article class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($url !== "") : ?>
        <a class="card-ui-link" href="<?= esc_url($url) ?>">
    <?php else : ?>
        <div class="card-ui-link">
    <?php endif; ?>

        <?php component::badge($args["type"] ?? ""); ?>

        <div class="card-ui-content">
            <?php component::title($args, 2, "card-ui-title"); ?>
            <?php component::text($args, "card-ui-description"); ?>
        </div>

    <?php if ($url !== "") : ?>
        </a>
    <?php else : ?>
        </div>
    <?php endif; ?>
</article>
