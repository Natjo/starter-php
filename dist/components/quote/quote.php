<?php
/** @var array $args */
$data = isset($args['args']) && is_array($args['args']) ? $args['args'] : [];
$quote = isset($data['quote']) ? trim((string) $data['quote']) : '';
$author = isset($data['author']) ? trim((string) $data['author']) : '';
$role = isset($data['role']) ? trim((string) $data['role']) : '';
$source = isset($data['source']) ? trim((string) $data['source']) : '';
$classes = isset($args['classes']) ? trim((string) $args['classes']) : '';
$attributes = isset($args['attributes']) ? trim((string) $args['attributes']) : '';

if ($quote === '') return;

$caption = $role !== '' ? $role : $source;
$root_class = 'quote' . ($classes !== '' ? ' ' . esc_attr($classes) : '');
?>

<figure class="<?= $root_class ?>"<?= $attributes !== '' ? ' ' . $attributes : '' ?>>
    <blockquote class="quote-quote">
        <?= (($quote)) ?>
    </blockquote>

    <?php if ($author !== '' || $caption !== '') : ?>
        <figcaption class="quote-caption">
            <?php if ($author !== '') : ?>
                <strong class="quote-author"><?= esc_html($author) ?></strong>
            <?php endif; ?>
            <?php if ($caption !== '') : ?>
                <span class="quote-source"><?= esc_html($caption) ?></span>
            <?php endif; ?>
        </figcaption>
    <?php endif; ?>
</figure>
