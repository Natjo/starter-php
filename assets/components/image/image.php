<?php
/** @var array $args */
$args = starter_args($args ?? null);
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
