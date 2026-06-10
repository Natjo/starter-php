<?php
/** @var string $type */
/** @var string $field_id */
/** @var string $name */
/** @var string $placeholder */
/** @var string $value */
/** @var bool $use_placeholder */
/** @var bool $required */
/** @var int $minlength */
/** @var mixed $min */
/** @var mixed $max */
/** @var string $pattern */
/** @var string $autocomplete */
/** @var string $typemismatch */
/** @var string $data_patternmismatch */
/** @var string $data_mandatory_attr */
/** @var string $field_aria */
/** @var string $aria_label */
?>
<input
    type="<?= esc_attr($type) ?>"
    id="<?= esc_attr($field_id) ?>"
    name="<?= esc_attr($name) ?>"
    <?php if ($value !== '') : ?>value="<?= esc_attr($value) ?>"<?php endif ?>
    <?php if ($use_placeholder) : ?>placeholder="<?= esc_attr($placeholder) ?>"<?php endif ?>
    <?php if ($required) : ?>required<?php endif ?>
    <?php if ($minlength > 0 && $type === 'text') : ?>minlength="<?= (int) $minlength ?>"<?php endif ?>
    <?php if ($min !== null && $type === 'number') : ?>min="<?= esc_attr((string) $min) ?>"<?php endif ?>
    <?php if ($max !== null && $type === 'number') : ?>max="<?= esc_attr((string) $max) ?>"<?php endif ?>
    <?php if ($pattern !== '') : ?>pattern="<?= esc_attr($pattern) ?>"<?php endif ?>
    <?php if ($autocomplete !== '') : ?>autocomplete="<?= esc_attr($autocomplete) ?>"<?php endif ?>
    <?php if ($typemismatch !== '') : ?>data-typemismatch="<?= esc_attr($typemismatch) ?>"<?php endif ?>
    <?php if ($pattern !== '' && $data_patternmismatch !== '') : ?>data-patternmismatch="<?= esc_attr($data_patternmismatch) ?>"<?php endif ?>
    <?= $data_mandatory_attr ?>
    <?= $field_aria ?>
    <?= $aria_label ?>
>
