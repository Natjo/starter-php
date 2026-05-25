<?php
/** @var array $args */
$args = isset($args) && is_array($args) ? $args : [];
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
$sanitizeClass = function ($value) {
    if (function_exists('sanitize_html_class')) {
        return sanitize_html_class($value);
    }

    return preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $value);
};

$label = isset($args['label']) ? trim((string) $args['label']) : '';
$name = isset($args['name']) && trim((string) $args['name']) !== '' ? trim((string) $args['name']) : '';
$type = isset($args['type']) ? strtolower(trim((string) $args['type'])) : 'text';
if ($type === '' || !preg_match('/^[a-z0-9_-]+$/', $type)) {
    $type = 'text';
}

$allowed_types = [
    'text', 'textarea', 'select', 'checkbox', 'checkboxes',
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
if (in_array($type, ['select', 'checkboxes', 'radios'], true) && empty($options)) {
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
$classes = isset($args['classes']) ? trim((string) $args['classes']) : '';
$attributes = isset($args['attributes']) ? trim((string) $args['attributes']) : '';
$mandatory_msg = isset($args['mandatory']) ? trim((string) $args['mandatory']) : '';
$data_mandatory_attr = $mandatory_msg !== '' ? ' data-mandatory="' . $escAttr($mandatory_msg) . '"' : '';

$field_id = $sanitizeClass($name);
$field_error_id = $field_id . '-error';
$group_label_id = $field_id . '-label';
$field_describedby = $field_error_id;
$is_group = in_array($type, ['checkboxes', 'radios'], true);
$is_single_checkbox = $type === 'checkbox';

$root_class = 'field';
if ($is_single_checkbox || $type === 'checkboxes') {
    $root_class .= ' checkbox';
}
if ($type === 'radios') {
    $root_class .= ' radio';
}
if ($classes !== '') {
    $root_class .= ' ' . $escAttr($classes);
}

$aria_label = ($label === '' && !$is_group && !$is_single_checkbox) ? ' aria-label="' . $escAttr($name) . '"' : '';

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
$field_aria = ' aria-describedby="' . $escAttr($field_describedby) . '"';
?>

<div class="<?= $root_class ?>"<?= $is_group ? ' role="group" aria-labelledby="' . $escAttr($group_label_id) . '" aria-describedby="' . $escAttr($field_error_id) . '"' : '' ?><?= $is_group ? $data_mandatory_attr : '' ?><?= ($is_group && $required && $type === 'checkboxes') ? ' data-required="true"' : '' ?><?= $attributes !== '' ? ' ' . $attributes : '' ?>>
    <?php if ($label !== '' && !$is_single_checkbox) : ?>
        <?php if ($is_group) : ?>
            <label id="<?= $escAttr($group_label_id) ?>"><?= $escHtml($label) ?></label>
        <?php else : ?>
            <label for="<?= $escAttr($field_id) ?>"><?= $escHtml($label) ?></label>
        <?php endif ?>
    <?php endif ?>

    <?php if ($type === 'textarea') : ?>
        <textarea
            id="<?= $escAttr($field_id) ?>"
            name="<?= $escAttr($name) ?>"
            rows="<?= (int) $rows ?>"
            <?php if ($required) : ?>required<?php endif ?>
            <?= $data_mandatory_attr ?>
            <?= $field_aria ?>
            <?= $aria_label ?>
        ></textarea>
        <div id="<?= $escAttr($field_error_id) ?>" class="invalid-msg" hidden></div>

    <?php elseif ($type === 'select') : ?>
        <select
            id="<?= $escAttr($field_id) ?>"
            name="<?= $escAttr($name) ?>"
            <?php if ($required) : ?>required<?php endif ?>
            <?= $data_mandatory_attr ?>
            <?= $field_aria ?>
            <?= $aria_label ?>
        >
            <?php foreach ($options as $option) :
                if (!is_array($option)) {
                    continue;
                }
                $option_label = isset($option['label']) ? trim((string) $option['label']) : (isset($option['name']) ? trim((string) $option['name']) : '');
                $option_value = isset($option['value']) ? (string) $option['value'] : $option_label;
                $hidden = !empty($option['hidden']);
                $selected = !empty($option['selected']);
                $disabled = !empty($option['disabled']);
            ?>
                <option
                    value="<?= $escAttr($option_value) ?>"
                    <?php if ($hidden) : ?>hidden<?php endif ?>
                    <?php if ($selected) : ?>selected<?php endif ?>
                    <?php if ($disabled) : ?>disabled<?php endif ?>
                ><?= $escHtml($option_label !== '' ? $option_label : $option_value) ?></option>
            <?php endforeach; ?>
        </select>
        <div id="<?= $escAttr($field_error_id) ?>" class="invalid-msg" hidden></div>

    <?php elseif ($type === 'checkboxes' || $type === 'radios') : ?>
        <?php $require_first_checkbox = $required && $type === 'checkboxes'; ?>
        <ul>
            <?php foreach ($options as $index => $option) :
                if (!is_array($option)) {
                    continue;
                }
                $option_label = isset($option['label']) ? trim((string) $option['label']) : (isset($option['name']) ? trim((string) $option['name']) : '');
                $option_value = isset($option['value']) ? (string) $option['value'] : (string) $index;
                $option_id = isset($option['id']) ? $sanitizeClass((string) $option['id']) : $field_id . '-' . $index;
                $option_name = $type === 'radios' ? $name : ($option['name'] ?? $name . '-' . $index);
                $checked = !empty($option['checked']) || !empty($option['selected']);
                $disabled = !empty($option['disabled']);
                if ($type === 'radios' && substr($option_name, -2) !== '[]') {
                    $option_name .= '[]';
                }
                if ($option_label === '') {
                    continue;
                }
            ?>
                <li>
                    <input
                        id="<?= $escAttr($option_id) ?>"
                        type="<?= $type === 'radios' ? 'radio' : 'checkbox' ?>"
                        name="<?= $escAttr($option_name) ?>"
                        value="<?= $escAttr($option_value) ?>"
                        <?php if ($checked) : ?>checked<?php endif ?>
                        <?php if ($disabled) : ?>disabled<?php endif ?>
                        <?php if ($required && $type === 'radios') : ?>required<?php endif ?>
                        <?php if ($require_first_checkbox) : ?>required<?php $require_first_checkbox = false; endif ?>
                    >
                    <label for="<?= $escAttr($option_id) ?>"><?= $escHtml($option_label) ?></label>
                </li>
            <?php endforeach; ?>
        </ul>
        <div id="<?= $escAttr($field_error_id) ?>" class="invalid-msg" hidden></div>

    <?php elseif ($is_single_checkbox) : ?>
        <input
            type="checkbox"
            id="<?= $escAttr($field_id) ?>"
            name="<?= $escAttr($name) ?>"
            value="1"
            <?php if ($checked) : ?>checked<?php endif ?>
            <?php if ($required) : ?>required<?php endif ?>
            <?= $data_mandatory_attr ?>
            <?= $field_aria ?>
        >
        <?php if ($label !== '') : ?>
            <label for="<?= $escAttr($field_id) ?>"><?= $escHtml($label) ?></label>
        <?php endif ?>
        <div id="<?= $escAttr($field_error_id) ?>" class="invalid-msg" hidden></div>

    <?php elseif (in_array($type, $input_types, true)) : ?>
        <input
            type="<?= $escAttr($type) ?>"
            id="<?= $escAttr($field_id) ?>"
            name="<?= $escAttr($name) ?>"
            <?php if ($use_placeholder) : ?>placeholder="<?= $escAttr($placeholder) ?>"<?php endif ?>
            <?php if ($required) : ?>required<?php endif ?>
            <?php if ($minlength > 0 && $type === 'text') : ?>minlength="<?= (int) $minlength ?>"<?php endif ?>
            <?php if ($min !== null && $type === 'number') : ?>min="<?= $escAttr((string) $min) ?>"<?php endif ?>
            <?php if ($max !== null && $type === 'number') : ?>max="<?= $escAttr((string) $max) ?>"<?php endif ?>
            <?php if ($pattern !== '') : ?>pattern="<?= $escAttr($pattern) ?>"<?php endif ?>
            <?php if ($autocomplete !== '') : ?>autocomplete="<?= $escAttr($autocomplete) ?>"<?php endif ?>
            <?php if ($typemismatch !== '' && $type === 'text') : ?>data-typemismatch="<?= $escAttr($typemismatch) ?>"<?php endif ?>
            <?php if ($pattern !== '' && $data_patternmismatch !== '') : ?>data-patternmismatch="<?= $escAttr($data_patternmismatch) ?>"<?php endif ?>
            <?= $data_mandatory_attr ?>
            <?= $field_aria ?>
            <?= $aria_label ?>
        >
        <div id="<?= $escAttr($field_error_id) ?>" class="invalid-msg" hidden></div>

    <?php endif ?>

    <?php if ($hint !== '') : ?>
        <small><?= $escHtml($hint) ?></small>
    <?php endif ?>
</div>
