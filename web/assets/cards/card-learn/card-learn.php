<?php
$args = normalize_args($args ?? null);
$title = htmlspecialchars((string) ($args["title"] ?? ($link["title"] ?? "")), ENT_QUOTES, "UTF-8");
$text = htmlspecialchars((string) ($args["text"] ?? ""), ENT_QUOTES, "UTF-8");
$url = (string) ($args["link"]["url"] ?? "");
$target = (string) ($args["link"]["target"] ?? "");
$tag = $url !== "" ? "a" : "div";
$icon = $args["icon"] ?? "";
?>

<article class="card-learn">
    <<?= $tag ?> class="card-content" <?php if ($url !== "") : ?> href="<?= esc_url($url) ?>" <?php if ($target !== "") : ?> target="<?= esc_attr($target) ?>" rel="noopener" <?php endif; ?><?php endif; ?>>
        <?php component::eyebrow($args); ?>
    
        <?php component::title($args, 3, ".title-3"); ?>
        <?php component::icon($icon, 24, 24, "icon-type"); ?>
        <?php component::text($args); ?>

        <?php component::icon("blank", 16, 16); ?>
    </<?= $tag ?>>
</article>