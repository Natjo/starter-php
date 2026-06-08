<?php
$args = normalize_args($args ?? null);
$classes = component::classes("tabs", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$items = [];
$title = !empty($args["title"]) ? (string) $args["title"] : "";
$default_panel_classes = $args["panel_classes"] ?? "";

if (!empty($args["items"]) && is_array($args["items"])) {
    foreach ($args["items"] as $item) {
        if (!is_array($item)) continue;

        $label = trim((string) ($item["label"] ?? $item["title"] ?? ""));
        $content = $item["content"] ?? $item["text"] ?? "";
        $disabled = !empty($item["disabled"]);
        $panel_classes = $item["panel_classes"] ?? $item["classes"] ?? $default_panel_classes;

        if ($label === "" || is_array($content) || trim((string) $content) === "") continue;

        $items[] = [
            "label" => $label,
            "content" => (string) $content,
            "disabled" => $disabled,
            "panel_classes" => $panel_classes,
        ];
    }
}

if (empty($items)) return;

$activeIndex = null;
foreach ($items as $index => $item) {
    if (empty($item["disabled"])) {
        $activeIndex = $index;
        break;
    }
}

if ($activeIndex === null) return;

static $tabsInstance = 0;
$tabsInstance++;
$uid = "tabs-" . $tabsInstance;
$tablist_id = "tablist-{$uid}";
?>

<div class="<?= $classes ?>" data-module="components/tab" data-context="@visible true"<?= $attributes ?>>
    <?php if ($title !== "") : ?>
        <h3 id="<?= esc_attr($tablist_id) ?>" class="tabs-title"><?= esc_html($title) ?></h3>
    <?php endif ?>

    <div role="tablist" class="tabs-list"<?= $title !== "" ? ' aria-labelledby="' . esc_attr($tablist_id) . '"' : "" ?>>
        <?php foreach ($items as $index => $item) :
            $tab_id = "tab-{$uid}-{$index}";
            $panel_id = "tabpanel-{$uid}-{$index}";
            $label = (string) $item["label"];
            $disabled = !empty($item["disabled"]);
            $isActive = $index === $activeIndex;
        ?>
            <button
                id="<?= esc_attr($tab_id) ?>"
                type="button"
                role="tab"
                aria-selected="<?= $isActive ? "true" : "false" ?>"
                aria-controls="<?= esc_attr($panel_id) ?>"
                <?= $disabled ? 'aria-disabled="true" disabled' : "" ?>
                <?= !$isActive ? 'tabindex="-1"' : "" ?>
            >
                <span class="focus"><?= esc_html($label) ?></span>
            </button>
        <?php endforeach ?>
    </div>

    <?php foreach ($items as $index => $item) :
        $tab_id = "tab-{$uid}-{$index}";
        $panel_id = "tabpanel-{$uid}-{$index}";
        $content = $item["content"];
        $panel_classes = component::classes(
            "tabs-panel",
            $item["panel_classes"] ?? "",
            $index !== $activeIndex ? "is-hidden" : ""
        );
    ?>
        <div
            id="<?= esc_attr($panel_id) ?>"
            role="tabpanel"
            tabindex="0"
            aria-labelledby="<?= esc_attr($tab_id) ?>"
            class="<?= $panel_classes ?>"
        >
            <?= wp_kses_post($content) ?>
        </div>
    <?php endforeach ?>
</div>
