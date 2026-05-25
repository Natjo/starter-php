<?php
$data = isset($quote) && is_array($quote) ? $quote : [];
$text = trim((string) ($data["text"] ?? ($data["quote"] ?? "")));
$author = trim((string) ($data["author"] ?? ""));
$role = trim((string) ($data["role"] ?? ""));
$source = trim((string) ($data["source"] ?? ""));
$classes = isset($classes) ? trim((string) $classes) : "";
$attributes = isset($attributes) ? trim((string) $attributes) : "";

if ($text === "") return;

$caption = $role !== '' ? $role : $source;
$rootClass = 'quote' . ($classes !== '' ? ' ' . htmlspecialchars($classes, ENT_QUOTES, "UTF-8") : '');
?>

<figure class="<?= $rootClass ?>"<?= $attributes !== '' ? ' ' . $attributes : '' ?>>
    <blockquote class="quote-text">
        <?= $text ?>
    </blockquote>

    <?php if ($author !== '' || $caption !== '') : ?>
        <figcaption class="quote-caption">
            <?php if ($author !== '') : ?>
                <strong class="quote-author"><?= htmlspecialchars($author, ENT_QUOTES, "UTF-8") ?></strong>
            <?php endif; ?>
            <?php if ($caption !== '') : ?>
                <span class="quote-role"><?= htmlspecialchars($caption, ENT_QUOTES, "UTF-8") ?></span>
            <?php endif; ?>
        </figcaption>
    <?php endif; ?>
</figure>
