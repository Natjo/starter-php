<?php
/** @var array $args */
$args = component::args($args ?? null);

$label = isset($args['label']) ? trim((string) $args['label']) : '';
$content = isset($args['content']) ? trim((string) $args['content']) : '';
$classes = component::classes('tooltip', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if ($label === '' || $content === '') return;

$uid = uniqid('tooltip-');
$anchor = '--' . $uid;
?>

<span class="<?= $classes ?>"<?= $attributes ?>>
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
