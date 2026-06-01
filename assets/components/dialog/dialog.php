<?php
$args = starter_args($args ?? null);
$uniqueId = function ($prefix = "") {
    if (function_exists("wp_unique_id")) {
        return wp_unique_id($prefix);
    }

    return uniqid($prefix);
};

$content = isset($args["content"]) ? (string) $args["content"] : "";
$title = isset($args["title"]) && is_scalar($args["title"]) ? trim((string) $args["title"]) : "";
$trigger_cfg = isset($args["trigger"]) && is_array($args["trigger"]) ? array_values($args["trigger"]) : ["btn", null, null];
$trigger_cfg = array_pad($trigger_cfg, 3, null);
$type = isset($trigger_cfg[0]) && strtolower((string) $trigger_cfg[0]) === "link" ? "link" : "btn";
$trigger_label = ($trigger_cfg[1] !== null && trim((string) $trigger_cfg[1]) !== "")
    ? trim((string) $trigger_cfg[1])
    : __("Open dialog", "starterkit");
$trigger_classes = ($trigger_cfg[2] !== null && trim((string) $trigger_cfg[2]) !== "")
    ? trim((string) $trigger_cfg[2])
    : "";
$classes = component::classes("dialog", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

$close_label = __("Close", "starterkit");
$id = "";

if ($content === "") return;
if ($id === "") $id = $uniqueId("dialog-");
$title_id = $id . "-title";
$content_id = $id . "-content";
$aria_label = $trigger_label !== "" ? $trigger_label : __("Dialog", "starterkit");
$aria = $title !== ""
    ? ' aria-labelledby="' . esc_attr($title_id) . '"'
    : ' aria-label="' . esc_attr($aria_label) . '"';

$trigger_extra = $trigger_classes !== "" ? " " . $trigger_classes : "";
?>

<?php if ($type === "link") : ?>
    <?php
    $dialog_link = [
        "title" => $trigger_label,
        "url" => "#" . $id,
        "target" => "",
    ];
    $link_classes = trim("dialog-trigger" . $trigger_extra);
    $link_attributes = sprintf(
        ' role="button" data-dialog-id="%s" aria-haspopup="dialog" aria-controls="%s" aria-expanded="false"',
        esc_attr($id),
        esc_attr($id)
    );
    component::link($dialog_link, $link_classes ?: null, null, $link_attributes);
    ?>
<?php else : ?>
    <?php
    $btn_classes = trim("dialog-trigger" . $trigger_extra);
    component::btn(
        $trigger_label,
        $btn_classes,
        [],
        'type="button"
        data-dialog-id="' . esc_attr($id) . '"
        aria-haspopup="dialog"
        aria-controls="' . esc_attr($id) . '"
        aria-expanded="false"'
    );
    ?>
<?php endif; ?>

<dialog
    id="<?= esc_attr($id) ?>"
    class="<?= $classes ?>"
    data-dialog
    data-module="components/dialog"
    <?= $aria ?><?= $attributes ?>>
    <div class="dialog-inner">
        <?php if ($title !== "") : ?>
            <h2 id="<?= esc_attr($title_id) ?>" class="dialog-title"><?= esc_html($title) ?></h2>
        <?php endif; ?>

        <div id="<?= esc_attr($content_id) ?>" class="dialog-content">
            <?= starter_kses_post($content) ?>
        </div>
        <form method="dialog" class="dialog-actions">
            <button type="submit" class="dialog-close" value="close" data-dialog-close>
                <?= esc_html($close_label) ?>
            </button>
        </form>
    </div>
</dialog>
