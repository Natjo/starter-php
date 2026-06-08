<?php
/** @var string $field_id */
/** @var string $name */
/** @var bool $required */
/** @var string $data_mandatory_attr */
/** @var string $field_aria */
/** @var string $aria_label */
/** @var array $options */
?>
<select
    id="<?= esc_attr($field_id) ?>"
    name="<?= esc_attr($name) ?>"
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
            value="<?= esc_attr($option_value) ?>"
            <?php if ($hidden) : ?>hidden<?php endif ?>
            <?php if ($selected) : ?>selected<?php endif ?>
            <?php if ($disabled) : ?>disabled<?php endif ?>
        ><?= esc_html($option_label !== '' ? $option_label : $option_value) ?></option>
    <?php endforeach; ?>
</select>
