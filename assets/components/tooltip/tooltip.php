<?php
/** @var array $args */
$args = starter_args($args ?? null);

$label = isset($args['label']) ? trim((string) $args['label']) : '';
$content = isset($args['content']) ? trim((string) $args['content']) : '';
$classes = component::classes('tooltip', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if ($label === '' || $content === '') return;

static $tooltipInstance = 0;
$tooltipInstance++;
$uid = 'tooltip-' . $tooltipInstance;
?>

<span class="<?= $classes ?>"<?= $attributes ?>>
    <button
        class="trigger-button"
        type="button"
        aria-describedby="<?= esc_attr($uid) ?>">
        <?= esc_html($label) ?>
    </button>
    <span
        class="tooltip-content"
        id="<?= esc_attr($uid) ?>"
        role="tooltip">
        <?= starter_kses_post($content) ?>
    </span>
</span>
