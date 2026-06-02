<?php
$args = starter_args($args ?? null);

$raw_date = $args["date"] ?? $args["datetime"] ?? $args["time"] ?? $args["value"] ?? "";
$format = isset($args["format"]) && is_scalar($args["format"]) && trim((string) $args["format"]) !== ""
    ? trim((string) $args["format"])
    : "d/m/Y";

if ($raw_date instanceof DateTimeInterface) {
    $date = DateTimeImmutable::createFromInterface($raw_date);
} elseif (is_scalar($raw_date) && trim((string) $raw_date) !== "") {
    try {
        $date = new DateTimeImmutable(trim((string) $raw_date));
    } catch (Exception) {
        $date = null;
    }
} else {
    $date = null;
}

if (!$date) return;

$label = isset($args["label"]) && is_scalar($args["label"]) && trim((string) $args["label"]) !== ""
    ? trim((string) $args["label"])
    : $date->format($format);
$datetime = isset($args["datetime"]) && is_scalar($args["datetime"]) && trim((string) $args["datetime"]) !== ""
    ? trim((string) $args["datetime"])
    : $date->format('Y-m-d');
$classes = component::classes("date", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<time class="date <?= $classes ?>" datetime="<?= esc_attr($datetime) ?>"<?= $attributes ?>><?= esc_html($label) ?></time>
