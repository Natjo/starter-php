<?php
$args = starter_args($args ?? null);
$name = isset($args['name']) ? sanitize_html_class((string) $args['name']) : '';
if ($name === '') return;
$type = isset($args['type']) ? trim((string) $args['type']) : '';
$size = isset($args['size']) ? trim((string) $args['size']) : '';
$label = isset($args['label']) && is_scalar($args['label']) ? trim((string) $args['label']) : '';
$classes = component::classes('picto', $type, $size, $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php component::icon($name, 24, 24, null, $label) ?>
</div>
