<?php
$args = component::args($args ?? null);
$data = isset($quote) && is_array($quote) ? $quote : [];
$text = trim((string) ($data["text"] ?? ($data["quote"] ?? "")));
$author = trim((string) ($data["author"] ?? ""));
$role = trim((string) ($data["role"] ?? ""));
$source = trim((string) ($data["source"] ?? ""));
$classes = component::classes('quote', $classes ?? '');
$attributes = component::attributes($attributes ?? []);

if ($text === "") return;

$caption = $role !== '' ? $role : $source;
?>

<figure class="<?= $classes ?>"<?= $attributes ?>>
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
