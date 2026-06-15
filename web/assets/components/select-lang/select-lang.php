<?php
$params = isset($params) && is_array($params) ? $params : [];
$select_lang_input = $params[0] ?? null;
if (!is_array($select_lang_input) || empty($select_lang_input)) return;
$args = isset($select_lang_input["languages"]) || isset($select_lang_input["args"])
    ? $select_lang_input
    : ["languages" => $select_lang_input];
if (($params[1] ?? null) !== null) $args["label"] = $params[1];
if (($params[2] ?? null) !== null) $args["classes"] = $params[2];
if (($params[3] ?? null) !== null) $args["attributes"] = $params[3];
$args = normalize_args($args);

$languages = isset($args["languages"]) && is_array($args["languages"])
    ? $args["languages"]
    : (isset($args["args"]) && is_array($args["args"]) ? $args["args"] : []);

if (empty($languages)) return;

$label = isset($args["label"]) && is_scalar($args["label"])
    ? trim((string) $args["label"])
    : "Choisir la langue";
$classes = component::classes("select-lang", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

$items = [];
foreach ($languages as $language) {
    if (!is_array($language)) continue;

    $code = isset($language["code"]) && is_scalar($language["code"])
        ? trim((string) $language["code"])
        : "";
    $item_label = isset($language["label"]) && is_scalar($language["label"])
        ? trim((string) $language["label"])
        : strtoupper($code);
    $url = isset($language["url"]) && is_scalar($language["url"])
        ? trim((string) $language["url"])
        : "";

    if ($item_label === "" || $url === "") continue;

    $items[] = [
        "code" => $code,
        "label" => $item_label,
        "url" => $url,
        "current" => !empty($language["current"]) || !empty($language["selected"]),
        "disabled" => !empty($language["disabled"]),
    ];
}

if (empty($items)) return;

$current_item = null;
foreach ($items as $item) {
    if ($item["current"]) {
        $current_item = $item;
        break;
    }
}
$current_item ??= $items[0];

static $select_lang_count = 0;
$select_lang_count++;
$uid = "select-lang-" . $select_lang_count;
?>

<div class="<?= $classes ?>" data-module="components/select-lang"<?= $attributes ?>>
    <button
        class="select-lang-trigger"
        type="button"
        aria-expanded="false"
        aria-controls="<?= esc_attr($uid) ?>"
        aria-haspopup="true"
        aria-label="<?= esc_attr($label) ?>"
    >
        <span class="select-lang-current"><?= esc_html($current_item["label"]) ?></span>
        <span class="select-lang-chevron" aria-hidden="true"></span>
        <?php component::icon("caret", 20,20) ?>
    </button>

    <ul class="select-lang-list" id="<?= esc_attr($uid) ?>" aria-label="<?= esc_attr($label) ?>" hidden>
        <?php foreach ($items as $item) : ?>
            <li>
                <?php if ($item["disabled"]) : ?>
                    <span
                        class="select-lang-option is-disabled"
                        <?= $item["code"] !== "" ? 'lang="' . esc_attr($item["code"]) . '"' : "" ?>
                        aria-disabled="true"
                    >
                        <?= esc_html($item["label"]) ?>
                    </span>
                <?php else : ?>
                    <a
                        class="select-lang-option<?= $item["current"] ? " is-current" : "" ?>"
                        href="<?= esc_url($item["url"]) ?>"
                        <?= $item["code"] !== "" ? 'lang="' . esc_attr($item["code"]) . '"' : "" ?>
                        <?= $item["current"] ? 'aria-current="page"' : "" ?>
                    >
                        <?= esc_html($item["label"]) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
