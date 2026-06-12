<?php
$picto_first = $params[0] ?? null;
if (empty($picto_first)) return;
if (is_array($picto_first)) {
    $picto_src = $picto_first;
    $picto_name = $picto_src["name"] ?? $picto_src["icon"] ?? "";
    $picto_type = $picto_src["type"] ?? ($params[1] ?? "");
    $picto_size = $picto_src["size"] ?? ($params[2] ?? "");
    $picto_classes = ($params[3] ?? null) ?? ($picto_src["classes"] ?? null);
    $picto_attributes = ($params[4] ?? null) ?? ($picto_src["attributes"] ?? null);
    $picto_label = $picto_src["label"] ?? null;
} else {
    $picto_name = $picto_first;
    $picto_type = $params[1] ?? "";
    $picto_size = $params[2] ?? "";
    $picto_classes = $params[3] ?? null;
    $picto_attributes = $params[4] ?? null;
    $picto_label = null;
}
if (empty($picto_name)) return;
$args = normalize_args([
    "name" => $picto_name,
    "type" => $picto_type,
    "size" => $picto_size,
    "classes" => $picto_classes,
    "attributes" => $picto_attributes,
    "label" => $picto_label,
]);
$name = isset($args['name']) ? sanitize_html_class((string) $args['name']) : '';
if ($name === '') return;
$type = isset($args['type']) ? trim((string) $args['type']) : '';
$size = isset($args['size']) ? trim((string) $args['size']) : '';
$label = isset($args['label']) && is_scalar($args['label']) ? trim((string) $args['label']) : '';
$classes = component::classes('picto', $type, $size, $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);
?>

<div class="<?= $classes ?>"<?= $attributes ?>>
    <?php component::icon($name, 24, 24, null, $label) ?>
</div>
