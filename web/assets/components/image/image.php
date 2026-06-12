<?php
/** @var array $args */
$image_first = $params[0] ?? null;
if (empty($image_first)) return;
$image_size = $params[1] ?? "full";
$image_classes = $params[2] ?? "";
$image_lazy = $params[3] ?? true;
$image_attributes = $params[4] ?? null;
if (is_array($image_first)) {
    $image_src = $image_first;
    $image_value = $image_src["image"] ?? $image_src["src"] ?? $image_src["url"] ?? "";
    $image_size = $image_src["size"] ?? $image_size;
    $image_classes = $image_classes !== "" ? $image_classes : ($image_src["classes"] ?? "");
    $image_lazy = array_key_exists("lazy", $image_src) ? (bool) $image_src["lazy"] : $image_lazy;
    $image_attributes = $image_attributes ?? ($image_src["attributes"] ?? null);
} else {
    $image_value = $image_first;
    $image_src = [];
}
if (empty($image_value)) return;
$args = normalize_args([
    "image" => $image_value,
    "size" => $image_size,
    "alt" => $image_src["alt"] ?? "",
    "classes" => $image_classes,
    "lazy" => $image_lazy,
    "decoding" => $image_src["decoding"] ?? "async",
    "fetchpriority" => $image_src["fetchpriority"] ?? null,
    "attributes" => $image_attributes,
]);
$input = $args["image"] ?? "";
$size = $args["size"] ?? "full";
$alt = isset($args["alt"]) && is_scalar($args["alt"]) ? trim((string) $args["alt"]) : "";

if ($input === "" || $input === null) return;

$image = lsd_get_thumb($input, $size);
if (empty($image[0])) return;

$src = $image[0];
$width = (int) ($image[1] ?? 0);
$height = (int) ($image[2] ?? 0);
if ($alt === "") {
    $alt = (string) ($image[3] ?? "");
}

if ($src === "") return;

$is_lazy = !empty($args["lazy"]);
$fetchpriority = isset($args["fetchpriority"]) && is_scalar($args["fetchpriority"])
    ? trim((string) $args["fetchpriority"])
    : ($is_lazy ? "low" : "high");
$fetchpriority = in_array($fetchpriority, ["high", "low", "auto"], true) ? $fetchpriority : "";
$decoding = isset($args["decoding"]) && is_scalar($args["decoding"]) ? trim((string) $args["decoding"]) : "async";
$decoding = in_array($decoding, ["async", "sync", "auto"], true) ? $decoding : "async";
$classes = !empty($args["classes"]) ? ' class="' . component::classes($args["classes"]) . '"' : "";
$dims = ($width > 0 ? ' width="' . $width . '"' : '') . ($height > 0 ? ' height="' . $height . '"' : '');
$attributes = component::attributes($args["attributes"] ?? []);
?>

<img<?= $classes ?> src="<?= esc_url($src) ?>" alt="<?= esc_attr($alt) ?>"<?= $dims ?><?= $is_lazy ? ' loading="lazy"' : '' ?><?= $decoding !== "" ? ' decoding="' . esc_attr($decoding) . '"' : '' ?><?= $fetchpriority !== "" ? ' fetchpriority="' . esc_attr($fetchpriority) . '"' : '' ?><?= $attributes ?>>
