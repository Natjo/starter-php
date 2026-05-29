<?php
$args = component::args($args ?? null);
$escAttr = function ($value) {
    if (function_exists("esc_attr")) {
        return esc_attr($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};

$name = isset($args['name']) ? trim((string) $args['name']) : '';
if ($name === '') return;
$type = isset($args['type']) ? trim((string) $args['type']) : '';
$size = isset($args['size']) ? trim((string) $args['size']) : '';
$classes = component::classes('picto', $type, $size, $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php component::icon($name, 24, 24) ?>
</div>
