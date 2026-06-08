<?php
$args = normalize_args($args ?? null, [
    "eyebrow" => "Un projet en tête ?",
    "title" => "Construisons quelque chose d'utile.",
    "text" => "Parlons de votre besoin, de vos contraintes et de la meilleure façon de le concrétiser.",
    "link" => [
        "title" => "Démarrer un projet",
        "url" => "/contact",
    ],
]);

$title = isset($args["title"]) && is_scalar($args["title"]) ? trim((string) $args["title"]) : "";
$text = isset($args["text"]) && is_scalar($args["text"]) ? trim((string) $args["text"]) : "";
$link = isset($args["link"]) && is_array($args["link"]) ? $args["link"] : [];
$linkTitle = isset($link["title"]) && is_scalar($link["title"]) ? trim((string) $link["title"]) : "";
$linkUrl = isset($link["url"]) && is_scalar($link["url"]) ? starter_safe_content_url((string) $link["url"]) : "";

if ($title === "") return;
?>

<aside class="prefooter" aria-labelledby="prefooter-title">
    <div class="prefooter-content">
        <?php component::eyebrow($args, "prefooter-eyebrow"); ?>

        <h2 id="prefooter-title" class="prefooter-title"><?= esc_html($title) ?></h2>

        <?php if ($text !== "") : ?>
            <p class="prefooter-text"><?= esc_html($text) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($linkTitle !== "" && $linkUrl !== "") : ?>
        <a class="prefooter-link" href="<?= esc_url($linkUrl) ?>">
            <span><?= esc_html($linkTitle) ?></span>
            <span class="prefooter-link-arrow" aria-hidden="true">→</span>
        </a>
    <?php endif; ?>
</aside>
