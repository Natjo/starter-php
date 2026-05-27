<?php
$args = isset($args) && is_array($args) ? $args : [];
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
$safeHtml = function ($value) {
    if (function_exists("wp_kses_post")) {
        return wp_kses_post($value);
    }

    return (string) $value;
};
$translate = function ($text) {
    if (function_exists("__")) {
        return __($text, "starterkit");
    }

    return $text;
};
$uniqueId = function ($prefix = "") {
    if (function_exists("wp_unique_id")) {
        return wp_unique_id($prefix);
    }

    return $prefix . bin2hex(random_bytes(4));
};

$content = isset($args["content"]) ? (string) $args["content"] : "";
$trigger_cfg = isset($args["trigger"]) && is_array($args["trigger"]) ? array_values($args["trigger"]) : ["btn", null, null];
$trigger_cfg = array_pad($trigger_cfg, 3, null);
$type = isset($trigger_cfg[0]) && strtolower((string) $trigger_cfg[0]) === "link" ? "link" : "btn";
$trigger_label = ($trigger_cfg[1] !== null && trim((string) $trigger_cfg[1]) !== "")
    ? trim((string) $trigger_cfg[1])
    : $translate("Open dialog");
$trigger_classes = ($trigger_cfg[2] !== null && trim((string) $trigger_cfg[2]) !== "")
    ? trim((string) $trigger_cfg[2])
    : "";
$classes = !empty($args["classes"]) ? " " . (string) $args["classes"] : "";
$attributes = !empty($args["attributes"]) ? (string) $args["attributes"] : "";

$close_label = $translate("Close");
$id = "";

if ($content === "") return;
if ($id === "") $id = $uniqueId("dialog-");
$content_id = $id . "-content";
$aria_label = $trigger_label !== "" ? $trigger_label : $translate("Dialog");

$trigger_extra = $trigger_classes !== "" ? " " . $trigger_classes : "";
?>

<?php if ($type === "link") : ?>
    <?php
    $dialog_link = [
        "title" => $trigger_label,
        "url" => "#",
        "target" => "",
    ];
    $link_classes = trim("dialog-trigger" . $trigger_extra);
    $link_attributes = sprintf(
        ' role="button" data-dialog-id="%s" aria-haspopup="dialog" aria-controls="%s" aria-expanded="false"',
        $escAttr($id),
        $escAttr($id)
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
        data-dialog-id="' . $escAttr($id) . '"
        aria-haspopup="dialog"
        aria-controls="' . $escAttr($id) . '"
        aria-expanded="false"'
    );
    ?>
<?php endif; ?>

<dialog
    id="<?= $escAttr($id) ?>"
    class="dialog<?= $escAttr($classes) ?>"
    data-dialog
    data-module="components/dialog"
    aria-label="<?= $escAttr($aria_label) ?>"
    aria-describedby="<?= $escAttr($content_id) ?>"
    <?= $attributes ?>>
    <div class="dialog-inner">
        <div id="<?= $escAttr($content_id) ?>" class="dialog-content">
            <?= $safeHtml($content) ?>
        </div>
        <form method="dialog" class="dialog-actions">
            <button type="submit" class="dialog-close" value="close" data-dialog-close>
                <?= $escHtml($close_label) ?>
            </button>
        </form>
    </div>
</dialog>
