<?php
/** @var array $args */
$args = starter_args($args ?? null);
$options = isset($args['options']) && is_array($args['options'])
    ? $args['options']
    : (isset($args['args']) && is_array($args['args']) ? $args['args'] : []);
$label = isset($args['label']) ? trim((string) $args['label']) : '';
$name = isset($args['name']) && trim((string) $args['name']) !== '' ? trim((string) $args['name']) : 'select';
$name = preg_replace('/[^A-Za-z0-9_\-\[\]]/', '', $name);
$name = $name !== '' ? $name : 'select';
$placeholder = isset($args['placeholder']) ? trim((string) $args['placeholder']) : '';
$required = !empty($args['required']);
$disabled = !empty($args['disabled']);
$multiple = !empty($args['multiple']);
$autocomplete = isset($args['autocomplete']) && is_scalar($args['autocomplete']) ? trim((string) $args['autocomplete']) : '';
$aria_label = isset($args['aria_label']) && is_scalar($args['aria_label']) ? trim((string) $args['aria_label']) : '';
$aria_describedby = isset($args['aria_describedby']) && is_scalar($args['aria_describedby']) ? trim((string) $args['aria_describedby']) : '';
$mandatory = isset($args['mandatory']) && is_scalar($args['mandatory']) ? trim((string) $args['mandatory']) : '';
$classes = component::classes('select-custom', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if (empty($options)) return;

static $select_count = 0;
$select_count++;
$uid = 'select-custom-' . sanitize_html_class($name) . '-' . $select_count;
$has_selected = false;
foreach ($options as $option) {
    if (is_array($option) && !empty($option['selected'])) {
        $has_selected = true;
        break;
    }
}
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php if ($label !== '') : ?>
        <label for="<?= esc_attr($uid) ?>"><?= esc_html($label) ?></label>
    <?php endif; ?>

    <div class="select-field">
        <select id="<?= esc_attr($uid) ?>" name="<?= esc_attr($name . ($multiple && !str_ends_with($name, '[]') ? '[]' : '')) ?>"<?= $label === '' ? ' aria-label="' . esc_attr($aria_label !== '' ? $aria_label : $name) . '"' : '' ?><?= $aria_describedby !== '' ? ' aria-describedby="' . esc_attr($aria_describedby) . '"' : '' ?><?= $mandatory !== '' ? ' data-mandatory="' . esc_attr($mandatory) . '"' : '' ?><?= $required ? ' required' : '' ?><?= $disabled ? ' disabled' : '' ?><?= $multiple ? ' multiple' : '' ?><?= $autocomplete !== '' ? ' autocomplete="' . esc_attr($autocomplete) . '"' : '' ?>>
            <?php if ($placeholder !== '') : ?>
                <option value=""<?= $required ? ' disabled' : '' ?><?= !$has_selected ? ' selected' : '' ?>><?= esc_html($placeholder) ?></option>
            <?php endif; ?>
            <?php foreach ($options as $option) :
                if (!is_array($option)) continue;
                $option_label = isset($option['label']) ? trim((string) $option['label']) : (isset($option['name']) ? trim((string) $option['name']) : '');
                $option_value = isset($option['value']) ? (string) $option['value'] : $option_label;
                if ($option_label === '' && $option_value === '') continue;
                $hidden = !empty($option['hidden']);
                $selected = !empty($option['selected']);
                $disabled = !empty($option['disabled']);
            ?>
                <option value="<?= esc_attr($option_value) ?>"<?= $hidden ? ' hidden' : '' ?><?= $selected ? ' selected' : '' ?><?= $disabled ? ' disabled' : '' ?>>
                    <?= esc_html($option_label !== '' ? $option_label : $option_value) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
