<?php
/** @var array $args */
$label = isset($args['label']) ? trim((string) $args['label']) : '';
$content = isset($args['content']) ? trim((string) $args['content']) : '';
$classes = isset($args['classes']) ? trim((string) $args['classes']) : '';
$attributes = isset($args['attributes']) ? trim((string) $args['attributes']) : '';

if ($label === '' || $content === '') return;

$uid = uniqid('tooltip-');
$anchor = '--' . $uid;
$root_class = 'tooltip' . ($classes !== '' ? ' ' . esc_attr($classes) : '');
?>

<span class="<?= $root_class ?>"<?= $attributes !== '' ? ' ' . $attributes : '' ?>>
    <button
        class="trigger-button"
        type="button"
        aria-describedby="<?= esc_attr($uid) ?>"
        style="anchor-name: <?= esc_attr($anchor) ?>;">
        <?= esc_html($label) ?>
    </button>
    <span
        class="tooltip-content"
        id="<?= esc_attr($uid) ?>"
        role="tooltip"
        style="position-anchor: <?= esc_attr($anchor) ?>;">
        <?= wp_kses_post($content) ?>
    </span>
</span>
