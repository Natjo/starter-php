<?php
/** @var array $options */
/** @var string $field_id */
/** @var string $type */
/** @var string $name */
/** @var bool $required */
?>
<ul>
    <?php foreach ($options as $index => $option) :
        if (!is_array($option)) {
            continue;
        }
        $option_label = isset($option['label']) ? trim((string) $option['label']) : (isset($option['name']) ? trim((string) $option['name']) : '');
        $option_value = isset($option['value']) ? (string) $option['value'] : (string) $index;
        $option_id = isset($option['id']) ? sanitize_html_class((string) $option['id']) : $field_id . '-' . $index;
        $option_name = $type === 'radios' ? $name : ($option['name'] ?? $name . '-' . $index);
        $checked = !empty($option['checked']) || !empty($option['selected']);
        $disabled = !empty($option['disabled']);
        if ($option_label === '') {
            continue;
        }
    ?>
        <li>
            <input
                id="<?= esc_attr($option_id) ?>"
                type="<?= $type === 'radios' ? 'radio' : 'checkbox' ?>"
                name="<?= esc_attr($option_name) ?>"
                value="<?= esc_attr($option_value) ?>"
                <?php if ($checked) : ?>checked<?php endif ?>
                <?php if ($disabled) : ?>disabled<?php endif ?>
                <?php if ($required && $type === 'radios') : ?>required<?php endif ?>
            >
            <label for="<?= esc_attr($option_id) ?>"><?= esc_html($option_label) ?></label>
        </li>
    <?php endforeach; ?>
</ul>
