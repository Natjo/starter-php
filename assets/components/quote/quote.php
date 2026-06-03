<?php
$args = normalize_args($args ?? null);
$data = isset($quote) && is_array($quote) ? $quote : $args;
$text = trim((string) ($data["text"] ?? ($data["quote"] ?? "")));
$author = trim((string) ($data["author"] ?? ""));
$role = trim((string) ($data["role"] ?? ""));
$source = trim((string) ($data["source"] ?? ""));
$classes = component::classes('quote', $classes ?? '');
$attributes = component::attributes($attributes ?? []);

if ($text === "") return;
$caption = trim(implode(" - ", array_filter([$role, $source], static fn($value) => $value !== "")));
?>

<figure class="<?= $classes ?>"<?= $attributes ?>>
    <blockquote class="quote-text">
        <?= starter_kses_post($text) ?>
    </blockquote>

    <?php if ($author !== '' || $caption !== '') : ?>
        <figcaption class="quote-caption">
            <?php if ($author !== '') : ?>
                <cite class="quote-author"><?= esc_html($author) ?></cite>
            <?php endif; ?>
            <?php if ($caption !== '') : ?>
                <span class="quote-role"><?= esc_html($caption) ?></span>
            <?php endif; ?>
        </figcaption>
    <?php endif; ?>
</figure>
