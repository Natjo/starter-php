<?php
$args = component::args($args ?? null);
$classes = component::classes("tabs", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$items = !empty($args["items"]) && is_array($args["items"]) ? $args["items"] : [];
$title = !empty($args["title"]) ? $args["title"] : "";

if (empty($items)) return;

$uid = uniqid("tabs-");
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
            $label = $item["label"] ?? $item["title"] ?? "";
            if ($label === "") continue;
        ?>
            <button
                id="<?= esc_attr($tab_id) ?>"
                type="button"
                role="tab"
                aria-selected="<?= $index === 0 ? "true" : "false" ?>"
                aria-controls="<?= esc_attr($panel_id) ?>"
                <?= $index > 0 ? 'tabindex="-1"' : "" ?>
            >
                <span class="focus"><?= esc_html($label) ?></span>
            </button>
        <?php endforeach ?>
    </div>

    <?php foreach ($items as $index => $item) :
        $tab_id = "tab-{$uid}-{$index}";
        $panel_id = "tabpanel-{$uid}-{$index}";
        $label = $item["label"] ?? $item["title"] ?? "";
        if ($label === "") continue;

        $content = $item["content"] ?? $item["text"] ?? "";
    ?>
        <div
            id="<?= esc_attr($panel_id) ?>"
            role="tabpanel"
            tabindex="0"
            aria-labelledby="<?= esc_attr($tab_id) ?>"
            class="tabs-panel text rte<?= $index > 0 ? " is-hidden" : "" ?>"
        >
            <?= wp_kses_post($content) ?>
        </div>
    <?php endforeach ?>
</div>
