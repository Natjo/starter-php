<?php
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
$name = isset($args["name"]) && is_scalar($args["name"])
    ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $args["name"])
    : "language";
$classes = component::classes("select-lang", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

static $select_lang_count = 0;
$select_lang_count++;
$uid = "select-lang-" . $select_lang_count;
?>

<div class="<?= $classes ?>" data-module="components/select-lang"<?= $attributes ?>>
    <label class="sr-only" for="<?= esc_attr($uid) ?>"><?= esc_html($label) ?></label>

    <span class="select-lang-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="20" height="20" focusable="false">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M3 12h18M12 3c2.4 2.5 3.6 5.5 3.6 9S14.4 18.5 12 21M12 3C9.6 5.5 8.4 8.5 8.4 12s1.2 6.5 3.6 9"></path>
        </svg>
    </span>

    <select id="<?= esc_attr($uid) ?>" name="<?= esc_attr($name !== "" ? $name : "language") ?>">
        <?php foreach ($languages as $language) :
            if (!is_array($language)) continue;

            $code = isset($language["code"]) && is_scalar($language["code"])
                ? trim((string) $language["code"])
                : "";
            $option_label = isset($language["label"]) && is_scalar($language["label"])
                ? trim((string) $language["label"])
                : strtoupper($code);
            $url = isset($language["url"]) && is_scalar($language["url"])
                ? trim((string) $language["url"])
                : "";

            if ($option_label === "" || $url === "") continue;
        ?>
            <option
                value="<?= esc_url($url) ?>"
                <?= $code !== "" ? 'lang="' . esc_attr($code) . '"' : "" ?>
                <?= !empty($language["current"]) || !empty($language["selected"]) ? "selected" : "" ?>
                <?= !empty($language["disabled"]) ? "disabled" : "" ?>>
                <?= esc_html($option_label) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
