<?php
/** @var array $args */
$args = normalize_args($args ?? null);

$label = isset($args['label']) ? trim((string) $args['label']) : '';
$name = isset($args['name']) && trim((string) $args['name']) !== '' ? trim((string) $args['name']) : '';
$type = isset($args['type']) ? strtolower(trim((string) $args['type'])) : 'text';
if ($type === '' || !preg_match('/^[a-z0-9_-]+$/', $type)) {
    $type = 'text';
}

$allowed_types = [
    'text', 'textarea', 'select', 'select-custom', 'select-custom-full', 'checkbox', 'checkboxes',
    'radio', 'radios', 'number', 'email', 'url', 'tel', 'date', 'password',
];
if ($name === '' || !in_array($type, $allowed_types, true)) {
    return;
}

if ($type === 'radio') {
    $type = 'radios';
}

$required = !empty($args['required']);
$placeholder = isset($args['placeholder']) ? (string) $args['placeholder'] : '';
$typemismatch = isset($args['typemismatch']) ? trim((string) $args['typemismatch']) : '';
$autocomplete_types = ['text', 'email', 'tel', 'number', 'date', 'password'];
$autocomplete = (in_array($type, $autocomplete_types, true) && isset($args['autocomplete']))
    ? trim((string) $args['autocomplete'])
    : '';
$minlength = ($type === 'text' && !empty($args['minlength'])) ? (int) $args['minlength'] : 0;
$rows = ($type === 'textarea' && !empty($args['rows'])) ? max(2, (int) $args['rows']) : 4;
$options = !empty($args['options']) && is_array($args['options']) ? $args['options'] : [];
if (in_array($type, ['select', 'select-custom', 'select-custom-full', 'checkboxes', 'radios'], true) && empty($options)) {
    return;
}

$pattern = isset($args['pattern']) ? trim((string) $args['pattern']) : '';
$data_patternmismatch = ($pattern !== '' && isset($args['data_patternmismatch']))
    ? trim((string) $args['data_patternmismatch'])
    : '';
$min = ($type === 'number' && isset($args['min']) && $args['min'] !== '' && $args['min'] !== null)
    ? $args['min']
    : null;
$max = ($type === 'number' && isset($args['max']) && $args['max'] !== '' && $args['max'] !== null)
    ? $args['max']
    : null;
$hint = isset($args['hint']) ? trim((string) $args['hint']) : '';
$checked = !empty($args['checked']);
$attributes = component::attributes($args['attributes'] ?? []);
$mandatory_msg = isset($args['mandatory']) ? trim((string) $args['mandatory']) : '';
$default_mandatory_msg = isset($args['mandatory_msg']) ? trim((string) $args['mandatory_msg']) : '';
$required_msg = $mandatory_msg !== '' ? $mandatory_msg : $default_mandatory_msg;
$invalid_msg = $required ? $required_msg : '';
$data_mandatory_msg = $required ? $required_msg : $mandatory_msg;
$data_mandatory_attr = $data_mandatory_msg !== '' ? ' data-mandatory="' . esc_attr($data_mandatory_msg) . '"' : '';

static $formFieldInstance = 0;
$formFieldInstance++;
$field_base_id = sanitize_html_class($name);
$field_id = !empty($args['id']) ? sanitize_html_class((string) $args['id']) : '';
$field_id = $field_id !== '' ? $field_id : $field_base_id . '-' . $formFieldInstance;
$field_error_id = $field_id . '-error';
$field_hint_id = $field_id . '-hint';
$group_label_id = $field_id . '-label';
$field_describedby_ids = [$field_error_id];
if ($hint !== '') {
    $field_describedby_ids[] = $field_hint_id;
}
$field_describedby = implode(' ', $field_describedby_ids);
$is_group = in_array($type, ['checkboxes', 'radios'], true);
$is_single_checkbox = $type === 'checkbox';

$root_class = component::classes(
    'field',
    ($is_single_checkbox || $type === 'checkboxes') ? 'checkbox' : '',
    $type === 'radios' ? 'radio' : '',
    $args['classes'] ?? ''
);

$aria_label = ($label === '' && !$is_group && !$is_single_checkbox) ? ' aria-label="' . esc_attr($name) . '"' : '';

if (in_array($type, $autocomplete_types, true)) {
    if ($autocomplete === '' && $type === 'email') {
        $autocomplete = 'email';
    }
    if ($autocomplete === '' && $type === 'tel') {
        $autocomplete = 'tel';
    }
    if ($autocomplete === '' && $type === 'text' && $name === 'name') {
        $autocomplete = 'name';
    }
}

$input_types = ['text', 'email', 'url', 'tel', 'date', 'number', 'password'];
$placeholder_types = ['text', 'email', 'date', 'tel', 'number', 'password'];
$use_placeholder = $placeholder !== '' && in_array($type, $placeholder_types, true);
$field_aria = ' aria-describedby="' . esc_attr($field_describedby) . '"';

$field_template = match (true) {
    $type === 'textarea' => 'textarea',
    $type === 'select' => 'select',
    $type === 'select-custom' => 'select-custom',
    $type === 'select-custom-full' => 'select-custom-full',
    $type === 'checkboxes' || $type === 'radios' => 'choices',
    $is_single_checkbox => 'checkbox',
    in_array($type, $input_types, true) => 'input',
    default => '',
};

if ($field_template === '') {
    return;
}
?>

<div class="<?= $root_class ?>"<?= $is_group ? ' role="group" aria-labelledby="' . esc_attr($group_label_id) . '" aria-describedby="' . esc_attr($field_describedby) . '"' : '' ?><?= $is_group ? $data_mandatory_attr : '' ?><?= ($is_group && $required && $type === 'checkboxes') ? ' data-required="true"' : '' ?><?= $attributes ?>>
    <?php if ($label !== '' && !$is_single_checkbox && $type !== 'select-custom') : ?>
        <?php if ($is_group) : ?>
            <span id="<?= esc_attr($group_label_id) ?>"><?= esc_html($label) ?></span>
        <?php else : ?>
            <label for="<?= esc_attr($field_id) ?>"><?= esc_html($label) ?></label>
        <?php endif ?>
    <?php endif ?>

    <?php get_template_part("form/fields/{$field_template}", null, get_defined_vars()); ?>

    <?php if ($hint !== '') : ?>
        <small id="<?= esc_attr($field_hint_id) ?>"><?= esc_html($hint) ?></small>
    <?php endif ?>

    <div id="<?= esc_attr($field_error_id) ?>" class="invalid-msg" data-default="<?= esc_attr($invalid_msg) ?>" hidden><?= esc_html($invalid_msg) ?></div>
</div>
