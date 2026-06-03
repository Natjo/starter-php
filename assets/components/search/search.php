<?php
$args = normalize_args($args ?? null);
$label = isset($args["label"]) && trim((string) $args["label"]) !== "" ? trim((string) $args["label"]) : "Rechercher";
$placeholder = isset($args["placeholder"]) && trim((string) $args["placeholder"]) !== "" ? trim((string) $args["placeholder"]) : "";
$button_label = isset($args["button_label"]) && trim((string) $args["button_label"]) !== "" ? trim((string) $args["button_label"]) : $label;
$action = isset($args["action"]) && trim((string) $args["action"]) !== "" ? trim((string) $args["action"]) : "/";
$name = isset($args["name"]) && trim((string) $args["name"]) !== "" ? trim((string) $args["name"]) : "s";
$value = isset($args["value"]) ? trim((string) $args["value"]) : "";
$autocomplete = isset($args["autocomplete"]) && trim((string) $args["autocomplete"]) !== "" ? trim((string) $args["autocomplete"]) : "off";
$hide_label = !empty($args["hide_label"]);
$classes = component::classes("search", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$label_class = component::classes("search__label", $hide_label ? "sr-only" : "");

static $searchInstance = 0;
$searchInstance++;
$uid = "search-" . $searchInstance;
?>

<form class="<?= $classes ?>" action="<?= esc_url($action) ?>" method="get" role="search"<?= $attributes ?>>
    <label class="<?= $label_class ?>" for="<?= esc_attr($uid) ?>"><?= esc_html($label) ?></label>
    <div class="search__field">
        <input
            id="<?= esc_attr($uid) ?>"
            class="search__input"
            type="search"
            name="<?= esc_attr($name) ?>"
            value="<?= esc_attr($value) ?>"
            autocomplete="<?= esc_attr($autocomplete) ?>"
            placeholder="<?= esc_attr($placeholder) ?>">
        <button class="search__button" type="submit"><?= esc_html($button_label) ?></button>
    </div>
</form>
