<?php
$args = $params[0] ?? null;
if (is_string($args) || is_numeric($args)) $args = ["date" => $args];
if (!is_array($args)) return;
if (($params[1] ?? null) !== null) $args["classes"] = $params[1];
if (($params[2] ?? null) !== null) $args["attributes"] = $params[2];
$args = normalize_args($args);

$value = $args["date"] ?? $args["text"] ?? "";
$value = is_scalar($value) ? trim((string) $value) : "";
if ($value === "") return;

// Attribut datetime lisible par la machine (optionnel, auto-détecté sinon).
$datetime = $args["datetime"] ?? "";
$datetime = is_scalar($datetime) ? trim((string) $datetime) : "";
if ($datetime === "") {
    $ts = strtotime($value);
    if ($ts !== false) $datetime = date("Y-m-d", $ts);
}

$classes = component::classes("date", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<time class="<?= $classes ?>"<?= $datetime !== "" ? ' datetime="' . esc_attr($datetime) . '"' : "" ?><?= $attributes ?>><?= wp_kses_post($value) ?></time>
