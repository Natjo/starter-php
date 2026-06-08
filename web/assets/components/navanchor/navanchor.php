<?php
$args = normalize_args($args ?? null);
$source = $args["items"] ?? [];

if (is_array($source) && isset($source["items"]) && is_array($source["items"])) {
    $args = array_replace($source, [
        "classes" => $args["classes"] ?? ($source["classes"] ?? null),
        "attributes" => $args["attributes"] ?? ($source["attributes"] ?? null),
        "label" => $source["label"] ?? ($args["label"] ?? null),
    ]);
}

$items = [];
$classes = component::classes("navanchor", "menu-navigation", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$label = isset($args["label"]) && is_scalar($args["label"]) ? trim((string) $args["label"]) : "";

if (!empty($args["items"]) && is_array($args["items"])) {
    foreach ($args["items"] as $item) {
        if (!is_array($item)) continue;

        $anchor = trim((string) ($item["anchor"] ?? $item["id"] ?? ""));
        $name = trim((string) ($item["name"] ?? $item["label"] ?? $item["title"] ?? ""));

        $anchor = ltrim($anchor, "#");
        $anchor = sanitize_html_class($anchor);

        if ($anchor === "" || $name === "") continue;

        $items[] = [
            "anchor" => $anchor,
            "name" => $name,
        ];
    }
}

if (empty($items)) return;

static $navanchorInstance = 0;
$navanchorInstance++;
$title_id = "navanchor-title-" . $navanchorInstance;
?>

<nav class="<?= $classes ?>" data-module="components/navanchor" aria-labelledby="<?= esc_attr($title_id) ?>"<?= $attributes ?>>
    <p class="navanchor-title" id="<?= esc_attr($title_id) ?>"><?= esc_html($label) ?></p>
    <ul>
        <?php foreach ($items as $item) : ?>
            <li class="list-item-navigation">
                <a
                    href="#<?= esc_attr($item["anchor"]) ?>"
                ><?= esc_html($item["name"]) ?></a>
            </li>
        <?php endforeach ?>
    </ul>
</nav>
