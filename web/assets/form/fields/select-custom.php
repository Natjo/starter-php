<?php
/** @var array $options */
/** @var string $label */
/** @var string $name */
/** @var bool $required */
/** @var string $placeholder */
/** @var string $required_msg */
/** @var string $autocomplete */
/** @var string $field_describedby */
/** @var array $args */
?>
<?php get_template_part('components/select-custom/select-custom', null, [
    'args' => $options,
    'label' => $label,
    'name' => $name,
    'required' => $required,
    'placeholder' => $placeholder,
    'mandatory' => $required_msg,
    'autocomplete' => $autocomplete,
    'aria_describedby' => $field_describedby,
    'aria_label' => $label === '' ? $name : null,
    'classes' => $args['select_classes'] ?? null,
]); ?>
