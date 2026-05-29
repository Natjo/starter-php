<?php
$args = component::args($args ?? null);
$escAttr = function ($value) {
    if (function_exists("esc_attr")) {
        return esc_attr($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};
$escHtml = function ($value) {
    if (function_exists("esc_html")) {
        return esc_html($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};
$translate = function ($text) {
    if (function_exists("__")) {
        return __($text, "starterkit");
    }

    return $text;
};

$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$classes = component::classes("navanchor", "menu-navigation", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$label = !empty($args["label"]) ? $args["label"] : $translate("Table des matières");

if (empty($items)) return;
?>

<nav class="<?= $classes ?>" data-module="components/navanchor" aria-label="<?= $escAttr($label) ?>"<?= $attributes ?>>
    <p class="navanchor-title"><?= $escHtml($label) ?></p>
    <ul>
        <?php foreach ($items as $i => $item) :
            if (!is_array($item)) continue;
            $anchor = $item["anchor"] ?? $item["id"] ?? "";
            $name = $item["name"] ?? $item["label"] ?? $item["title"] ?? "";
            if ($anchor === "" || $name === "") continue;
            $is_first = $i === 0;
        ?>
            <li class="list-item-navigation">
                <a
                    href="#<?= $escAttr($anchor) ?>"
                    <?= $is_first ? 'class="active" aria-current="location"' : "" ?>
                ><?= $escHtml($name) ?></a>
            </li>
        <?php endforeach ?>
    </ul>
</nav>
