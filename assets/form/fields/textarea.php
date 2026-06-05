<?php
/** @var string $field_id */
/** @var string $name */
/** @var int $rows */
/** @var bool $required */
/** @var string $data_mandatory_attr */
/** @var string $field_aria */
/** @var string $aria_label */
?>
<textarea
    id="<?= esc_attr($field_id) ?>"
    name="<?= esc_attr($name) ?>"
    rows="<?= (int) $rows ?>"
    <?php if ($required) : ?>required<?php endif ?>
    <?= $data_mandatory_attr ?>
    <?= $field_aria ?>
    <?= $aria_label ?>
></textarea>
