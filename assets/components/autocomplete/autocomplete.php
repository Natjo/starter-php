<?php
/** @var array $args */
/*https://access42.net/concevoir-un-composant-d-auto-completion-accessible/*/

$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$label = isset($args["label"]) ? (string) $args["label"] : "";
$name = !empty($args["name"]) ? (string) $args["name"] : "autocomplete";
$placeholder = isset($args["placeholder"]) ? (string) $args["placeholder"] : __("Sélectionner", "starterkit");
$classes = isset($args["classes"]) ? trim((string) $args["classes"]) : "";
$attributes = isset($args["attributes"]) ? trim((string) $args["attributes"]) : "";

if (empty($items)) return;

$uid = uniqid();
$input_id = $uid . "-" . sanitize_html_class($name);
$menu_id = "autocomplete-options--" . $input_id;

$root_class = "autocomplete-field" . ($classes !== "" ? " " . esc_attr($classes) : "");
?>

<div class="<?= $root_class ?>" data-module="components/autocomplete"<?= $attributes !== "" ? " " . $attributes : "" ?>>
    <?php if ($label !== "") : ?>
        <label for="<?= esc_attr($input_id) ?>"><?= esc_html($label) ?></label>
    <?php endif ?>

    <select name="<?= esc_attr($name) ?>" aria-hidden="true" tabindex="-1" class="sr-only">
        <option value=""><?= esc_html($placeholder) ?></option>
        <?php foreach ($items as $item) :
            $value = isset($item["value"]) ? (string) $item["value"] : "";
            $itemName = isset($item["name"]) ? (string) $item["name"] : "";
            $alt = isset($item["alt"]) ? (string) $item["alt"] : "";
            if ($itemName === "" || $value === "") continue;
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
            aria-owns="<?= esc_attr($menu_id) ?>"
            aria-expanded="false">

        <?php component::icon('caret', 13, 8); ?>

        <ul id="<?= esc_attr($menu_id) ?>" role="listbox" class="hidden"></ul>

        <div aria-live="polite" role="status" class="sr-only"></div>
    </div>
</div>
