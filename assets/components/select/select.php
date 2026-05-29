<?php
/** @var array $args */
$args = component::args($args ?? null);
$escAttr = function ($value) {
    if (function_exists('esc_attr')) {
        return esc_attr($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$escHtml = function ($value) {
    if (function_exists('esc_html')) {
        return esc_html($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$options = isset($args['args']) && is_array($args['args']) ? $args['args'] : [];
$label = isset($args['label']) ? trim((string) $args['label']) : '';
$name = isset($args['name']) && trim((string) $args['name']) !== '' ? trim((string) $args['name']) : 'select';
$classes = component::classes('select', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if (empty($options)) return;

$uid = uniqid('select-');
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($label !== '') : ?>
        <label for="<?= $escAttr($uid) ?>"><?= $escHtml($label) ?></label>
    <?php endif; ?>

    <div class="select-field">
        <select id="<?= $escAttr($uid) ?>" name="<?= $escAttr($name) ?>"<?= $label === '' ? ' aria-label="' . $escAttr($name) . '"' : '' ?>>
            <?php foreach ($options as $option) :
                if (!is_array($option)) continue;
                $option_label = isset($option['name']) ? trim((string) $option['name']) : '';
                $option_value = isset($option['value']) ? (string) $option['value'] : $option_label;
                if ($option_label === '' && $option_value === '') continue;
                $selected = !empty($option['selected']);
                $disabled = !empty($option['disabled']);
            ?>
                <option value="<?= $escAttr($option_value) ?>"<?= $selected ? ' selected' : '' ?><?= $disabled ? ' disabled' : '' ?>>
                    <?= $escHtml($option_label !== '' ? $option_label : $option_value) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
