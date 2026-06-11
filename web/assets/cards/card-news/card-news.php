<?php
$args = normalize_args($args ?? null);
$url = (string) ($args["link"]["url"] ?? "");
$target = (string) ($args["link"]["target"] ?? "");
$tag = $url !== "" ? "a" : "div";
?>

<article class="card-news">
    <<?= $tag ?> class="card-content"<?php if ($url !== "") : ?> href="<?= esc_url($url) ?>"<?php if ($target !== "") : ?> target="<?= esc_attr($target) ?>" rel="noopener"<?php endif; ?><?php endif; ?>>
        <div class="card-header">
            <?php if (!empty($args["source"])) : ?>
                <div class="source">
                    <?= $args["source"] ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($args["date"])) : ?>
                <div class="date">
                    <?= $args["date"] ?>
                </div>
            <?php endif; ?>
        </div>

        <?php component::title($args, 3, ".title-3"); ?>

        <?php component::text($args); ?>
        
        <?php component::icon("blank", 16, 16); ?>
    </<?= $tag ?>>
</article>