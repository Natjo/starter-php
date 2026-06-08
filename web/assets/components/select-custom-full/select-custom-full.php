<?php
/** @var array $args */
$args = normalize_args($args ?? null);

$options = isset($args['options']) && is_array($args['options'])
    ? $args['options']
    : (isset($args['args']) && is_array($args['args']) ? $args['args'] : []);
$label = isset($args['label']) ? trim((string) $args['label']) : '';
$placeholder = isset($args['placeholder']) ? trim((string) $args['placeholder']) : $label;
$aria_label = isset($args['aria_label']) ? trim((string) $args['aria_label']) : $label;
$name = isset($args['name']) ? trim((string) $args['name']) : '';
$required = !empty($args['required']);
$mandatory = isset($args['mandatory']) ? trim((string) $args['mandatory']) : '';
$multi = !empty($args['multi']) || !empty($args['multiple']);
$classes = component::classes('select-custom-full', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if (empty($options)) return;

$uid = !empty($args['id']) ? sanitize_html_class((string) $args['id']) : uniqid();
$btn_id = $uid;
$listbox_id = $uid . '-listbox';
$describedby = isset($args['aria_describedby']) ? trim((string) $args['aria_describedby']) : '';

$active_descendant_id = '';
$selected_names = [];
$selected_values = [];
foreach ($options as $i => $opt) {
    if (empty($opt['selected'])) continue;
    if ($active_descendant_id === '') $active_descendant_id = $uid . '-' . $i;
    $option_label = !empty($opt['name']) ? (string) $opt['name'] : (!empty($opt['label']) ? (string) $opt['label'] : '');
    if ($option_label !== '') $selected_names[] = $option_label;
    if (isset($opt['value'])) {
        $selected_values[] = (string) $opt['value'];
    } elseif ($option_label !== '') {
        $selected_values[] = $option_label;
    }
    if (!$multi) break;
}
$initial_label = !empty($selected_names) ? implode(', ', $selected_names) : $placeholder;
$initial_value = implode(',', $selected_values);
$field_name = $multi && $name !== '' && !str_ends_with($name, '[]') ? $name . '[]' : $name;

?>

<div class="<?= $classes ?>" data-module="components/select-custom-full" data-placeholder="<?= esc_attr($placeholder) ?>"<?= $required ? ' data-field-required="true"' : '' ?><?= $mandatory !== '' ? ' data-mandatory="' . esc_attr($mandatory) . '"' : '' ?><?= $attributes ?>>
    <?php if ($name !== '') : ?>
        <?php if ($multi) : ?>
            <span hidden data-field-values data-name="<?= esc_attr($field_name) ?>">
                <?php foreach ($selected_values as $selected_value) : ?>
                    <input type="hidden" name="<?= esc_attr($field_name) ?>" value="<?= esc_attr($selected_value) ?>" data-field-value>
                <?php endforeach; ?>
            </span>
        <?php else : ?>
            <input
                type="hidden"
                name="<?= esc_attr($name) ?>"
                value="<?= esc_attr($initial_value) ?>"
                data-field-value
            >
        <?php endif ?>
    <?php endif ?>
    <button
        type="button"
        role="combobox"
        id="<?= esc_attr($btn_id) ?>"
        value="<?= esc_attr($initial_value) ?>"
        data-field-control
        aria-controls="<?= esc_attr($listbox_id) ?>"
        aria-haspopup="listbox"
        <?= $aria_label !== '' ? 'aria-label="' . esc_attr($aria_label) . '"' : '' ?>
        <?= $required ? 'aria-required="true"' : '' ?>
        tabindex="0"
        <?= $describedby !== '' ? 'aria-describedby="' . esc_attr($describedby) . '"' : '' ?>
        <?= $active_descendant_id !== '' ? 'aria-activedescendant="' . esc_attr($active_descendant_id) . '"' : '' ?>
        aria-expanded="false">
        <?= esc_html($initial_label) ?>
    </button>
    <div aria-live="assertive" role="alert" class="sr-only" data-select-announce></div>
    <ul role="listbox" id="<?= esc_attr($listbox_id) ?>"<?= $multi ? ' aria-multiselectable="true"' : '' ?>>
        <?php foreach ($options as $i => $opt) :
            if (!is_array($opt)) continue;
            $option_name = isset($opt['name']) ? (string) $opt['name'] : '';
            $option_name = $option_name !== '' ? $option_name : (isset($opt['label']) ? (string) $opt['label'] : '');
            $value = isset($opt['value']) ? (string) $opt['value'] : $option_name;
            $selected = !empty($opt['selected']);
            $disabled = !empty($opt['disabled']);
            if ($option_name === '' && $value === '') continue;
        ?>
            <li
                role="option"
                id="<?= esc_attr($uid . '-' . $i) ?>"
                data-value="<?= esc_attr($value) ?>"
                <?= $selected ? 'aria-selected="true"' : '' ?>
                <?= $disabled ? 'aria-disabled="true"' : '' ?>><?= esc_html($option_name) ?></li>
        <?php endforeach ?>
    </ul>
</div>
