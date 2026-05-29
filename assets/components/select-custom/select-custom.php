<?php
/** @var array $args */
$args = component::args($args ?? null);
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

$options = isset($args['args']) && is_array($args['args']) ? $args['args'] : [];
$label = isset($args['label']) ? trim((string) $args['label']) : '';
$multi = !empty($args['multi']);
$classes = component::classes('select-custom', $args['classes'] ?? '');
$attributes = component::attributes($args['attributes'] ?? []);

if (empty($options)) return;

$uid = uniqid();
$btn_id = 'select-custom-' . $uid;
$listbox_id = 'listbox-' . $uid;

$active_descendant_id = '';
$selected_names = [];
foreach ($options as $i => $opt) {
    if (empty($opt['selected'])) continue;
    if ($active_descendant_id === '') $active_descendant_id = $uid . '-' . $i;
    if (!empty($opt['name'])) $selected_names[] = (string) $opt['name'];
    if (!$multi) break;
}
$initial_label = !empty($selected_names) ? implode(', ', $selected_names) : $label;

?>

<div class="<?= $classes ?>" data-module="components/select-custom" data-placeholder="<?= $escAttr($label) ?>"<?= $attributes ?>>
    <button
        role="combobox"
        id="<?= $escAttr($btn_id) ?>"
        value="<?= $escAttr($initial_label) ?>"
        aria-controls="<?= $escAttr($listbox_id) ?>"
        aria-haspopup="listbox"
        tabindex="0"
        <?= $active_descendant_id !== '' ? 'aria-activedescendant="' . $escAttr($active_descendant_id) . '"' : '' ?>
        aria-expanded="false">
        <?= $escHtml($initial_label) ?>
    </button>
    <div aria-live="assertive" role="alert" class="sr-only" data-select-announce></div>
    <ul role="listbox" id="<?= $escAttr($listbox_id) ?>"<?= $multi ? ' aria-multiselectable="true"' : '' ?>>
        <?php foreach ($options as $i => $opt) :
            if (!is_array($opt)) continue;
            $name = isset($opt['name']) ? (string) $opt['name'] : '';
            $value = isset($opt['value']) ? (string) $opt['value'] : $name;
            $selected = !empty($opt['selected']);
            $disabled = !empty($opt['disabled']);
            if ($name === '' && $value === '') continue;
        ?>
            <li
                role="option"
                id="<?= $escAttr($uid . '-' . $i) ?>"
                data-value="<?= $escAttr($value) ?>"
                <?= $selected ? 'aria-selected="true"' : '' ?>
                <?= $disabled ? 'aria-disabled="true"' : '' ?>><?= $escHtml($name) ?></li>
        <?php endforeach ?>
    </ul>
</div>
