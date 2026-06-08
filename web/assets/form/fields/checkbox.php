<?php
/** @var string $field_id */
/** @var string $name */
/** @var bool $checked */
/** @var bool $required */
/** @var string $data_mandatory_attr */
/** @var string $field_aria */
/** @var string $label */
?>
<input
    type="checkbox"
    id="<?= esc_attr($field_id) ?>"
    name="<?= esc_attr($name) ?>"
    value="1"
    <?php if ($checked) : ?>checked<?php endif ?>
    <?php if ($required) : ?>required<?php endif ?>
    <?= $data_mandatory_attr ?>
    <?= $field_aria ?>
>
<?php if ($label !== '') : ?>
    <label for="<?= esc_attr($field_id) ?>"><?= esc_html($label) ?></label>
<?php endif ?>
