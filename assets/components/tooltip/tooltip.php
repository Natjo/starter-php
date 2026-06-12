<?php
/** @var array $args */
$tooltip_label = $params[0] ?? null;
if (empty($tooltip_label)) return;
if (is_array($tooltip_label)) {
    $args = $tooltip_label;
    if (($params[1] ?? null) !== null) $args["content"] = $params[1];
    if (($params[2] ?? null) !== null) $args["classes"] = $params[2];
    if (($params[3] ?? null) !== null) $args["attributes"] = $params[3];
} else {
    $args = [
        "label" => $tooltip_label,
        "content" => $params[1] ?? null,
        "classes" => $params[2] ?? null,
        "attributes" => $params[3] ?? null,
    ];
}
$args = normalize_args($args);

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
        <?= wp_kses_post($content) ?>
    </span>
</span>
