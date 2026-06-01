<?php
/** @var array $args */
/*https://access42.net/concevoir-un-composant-d-auto-completion-accessible/*/
$args = starter_args($args ?? null);

$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$items = array_values(array_filter(array_map(static function ($item) {
    if (!is_array($item)) return null;

    $value = isset($item["value"]) && is_scalar($item["value"]) ? trim((string) $item["value"]) : "";
    $name = isset($item["name"]) && is_scalar($item["name"]) ? trim((string) $item["name"]) : "";
    $alt = isset($item["alt"]) && is_scalar($item["alt"]) ? trim((string) $item["alt"]) : "";

    return $value !== "" && $name !== "" ? ["value" => $value, "name" => $name, "alt" => $alt] : null;
}, $items)));
$label = isset($args["label"]) ? (string) $args["label"] : "";
$name = !empty($args["name"]) ? preg_replace('/[^a-zA-Z0-9_\-\[\]]/', '', trim((string) $args["name"])) : "autocomplete";
$name = $name !== "" ? $name : "autocomplete";
$placeholder = isset($args["placeholder"]) ? (string) $args["placeholder"] : __("Sélectionner", "starterkit");
$classes = component::classes("autocomplete-field", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

if (empty($items)) return;

$uid = uniqid();
$input_id = $uid . "-" . sanitize_html_class($name);
$menu_id = "autocomplete-options--" . $input_id;

?>

<div class="<?= $classes ?>" data-module="components/autocomplete"<?= $attributes ?>>
    <?php if ($label !== "") : ?>
        <label for="<?= esc_attr($input_id) ?>"><?= esc_html($label) ?></label>
    <?php endif ?>

    <select name="<?= esc_attr($name) ?>" aria-hidden="true" tabindex="-1" class="sr-only">
        <option value=""><?= esc_html($placeholder) ?></option>
        <?php foreach ($items as $item) :
            $value = $item["value"];
            $itemName = $item["name"];
            $alt = $item["alt"];
        ?>
            <option value="<?= esc_attr($value) ?>"<?= $alt !== "" ? ' data-alt="' . esc_attr($alt) . '"' : "" ?>><?= esc_html($itemName) ?></option>
        <?php endforeach ?>
    </select>

    <div class="autocomplete">
        <input
            type="text"
            id="<?= esc_attr($input_id) ?>"
            role="combobox"
            autocomplete="off"
            autocapitalize="none"
            aria-autocomplete="list"
            aria-controls="<?= esc_attr($menu_id) ?>"
            aria-owns="<?= esc_attr($menu_id) ?>"
            placeholder="<?= esc_attr($placeholder) ?>"
            aria-expanded="false">

        <?php component::icon('caret', 13, 8); ?>

        <ul id="<?= esc_attr($menu_id) ?>" role="listbox" class="hidden"></ul>

        <div aria-live="polite" role="status" class="sr-only"></div>
    </div>
</div>
