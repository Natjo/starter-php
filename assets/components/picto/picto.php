<?php
$args = isset($args) && is_array($args) ? $args : [];
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
$classes = isset($args['classes']) ? trim((string) $args['classes']) : '';
$attributes = isset($args['attributes']) ? trim((string) $args['attributes']) : '';
$type_class = $type !== '' ? ' ' . $escAttr($type) : '';
$size_class = $size !== '' ? ' ' . $escAttr($size) : '';
$extra_class = $classes !== '' ? ' ' . $escAttr($classes) : '';
?>

<div class="picto<?= $type_class . $size_class . $extra_class ?>"<?= $attributes !== '' ? ' ' . $attributes : '' ?>>
    <?php component::icon($name, 24, 24) ?>
</div>
