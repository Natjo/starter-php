<?php
$args = component::args($args ?? null);
$label = isset($label) && trim((string) $label) !== "" ? trim((string) $label) : "Rechercher";
$placeholder = isset($placeholder) && trim((string) $placeholder) !== "" ? trim((string) $placeholder) : "";
$button_label = isset($button_label) && trim((string) $button_label) !== "" ? trim((string) $button_label) : $label;
$action = isset($action) && trim((string) $action) !== "" ? trim((string) $action) : "/";
$classes = component::classes("search", $classes ?? "");
$attributes = component::attributes($attributes ?? []);
$uid = uniqid("search-");
?>

<form class="<?= $classes ?>" action="<?= esc_url($action) ?>" method="get" role="search"<?= $attributes ?>>
    <label class="search__label" for="<?= esc_attr($uid) ?>"><?= esc_html($label) ?></label>
    <div class="search__field">
        <input
            id="<?= esc_attr($uid) ?>"
            class="search__input"
            type="search"
            name="s"
            placeholder="<?= esc_attr($placeholder) ?>">
        <button class="search__button" type="submit"><?= esc_html($button_label) ?></button>
    </div>
</form>
