<?php
$args = isset($args) && is_array($args) ? $args : [];
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : [];
$url = htmlspecialchars((string) ($link["url"] ?? "#"), ENT_QUOTES, "UTF-8");
$title = htmlspecialchars((string) ($args["title"] ?? ($link["title"] ?? "")), ENT_QUOTES, "UTF-8");
$text = htmlspecialchars((string) ($args["text"] ?? ""), ENT_QUOTES, "UTF-8");
$hasImage = !empty($args["images"]);
?>

<article class="card-news">
    <a href="<?= $url ?>">
        <?php if ($hasImage) : ?>
            <?php component("picture", $args); ?>
        <?php endif; ?>

        <?php if ($title !== "" || $text !== "") : ?>
            <div class="card-news-content">
                <?php if ($title !== "") : ?>
                    <h3><?= $title ?></h3>
                <?php endif; ?>
                <?php if ($text !== "") : ?>
                    <p><?= $text ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </a>
</article>
