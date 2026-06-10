<?php
/** @var array $options */
/** @var string $label */
/** @var string $placeholder */
/** @var string $name */
/** @var bool $required */
/** @var string $required_msg */
/** @var string $field_id */
/** @var string $field_describedby */
/** @var array $args */
?>
<?php get_template_part('components/select-custom-full/select-custom-full', null, [
    'options' => $options,
    'label' => $label,
    'placeholder' => $placeholder !== '' ? $placeholder : $label,
    'name' => $name,
    'required' => $required,
    'mandatory' => $required_msg,
    'multiple' => !empty($args['multiple']) || !empty($args['multi']),
    'id' => $field_id,
    'aria_describedby' => $field_describedby,
    'aria_label' => $label,
    'classes' => $args['select_classes'] ?? null,
]); ?>
