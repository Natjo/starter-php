<?php
/** @var array $args */
$args = component::args($args ?? null);
// Accepts:
// - $args["images"] as ["desktop" => id|path, "mobile" => id|path], scalar, or indexed array
// - Or directly an id|path as $args itself (backward-friendly)
$raw = is_array($args) ? ($args["images"] ?? null) : $args;

if (!is_array($raw) || (!array_key_exists("desktop", $raw) && !array_key_exists("mobile", $raw))) {
    $raw = ["desktop" => is_array($raw) ? reset($raw) : $raw];
}

$classesValue = is_array($args) ? ($args["classes"] ?? "") : "";
$placeholder = is_array($args) ? !empty($args["placeholder"]) : false;
$breakpoint = (int) (is_array($args) ? ($args["breakpoint"] ?? 768) : 768);
$lazyFlag = is_array($args) ? !empty($args["lazy"]) : true;
$desktopSize = is_array($args) ? ($args["desktop_size"] ?? "full") : "full";
$mobileSize = is_array($args) ? ($args["mobile_size"] ?? "full") : "full";
$escape = function ($value) {
    if (function_exists("esc_attr")) {
        return esc_attr($value);
    }

    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
};

if (empty($raw["desktop"]) && empty($raw["mobile"])) {
    if ($placeholder) {
        echo '<picture class="' . component::classes("placeholder", $classesValue) . '"></picture>';
    }
    return;
}

$resolve = function ($id, $size) {
    if (is_string($id) && !is_numeric($id)) {
        if (!preg_match('#^(https?:)?//#', $id) && $id[0] !== "/") {
            $id = dist_asset_url("img/" . $id);
        }

        $width = 0;
        $height = 0;
        if (preg_match('/(\d+)x(\d+)/', basename($id), $m)) {
            [$width, $height] = [(int) $m[1], (int) $m[2]];
        }
        // Try to resolve real dimensions for local files.
        if ($width === 0 || $height === 0) {
            $local_path = null;
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $root = defined("ABSPATH") ? ABSPATH : (defined("APP_ROOT") ? APP_ROOT . "/" : getcwd() . "/");
            if ($host && strpos($id, "://" . $host . "/") !== false) {
                $local_path = str_replace("https://" . $host . "/", $root, $id);
                $local_path = str_replace("http://" . $host . "/", $root, $local_path);
            } elseif (strpos($id, '/') === 0) {
                $local_path = rtrim($root, "/") . $id;
            } elseif (file_exists($id)) {
                $local_path = $id;
            }
            if ($local_path && file_exists($local_path)) {
                $size_info = @getimagesize($local_path);
                if (!empty($size_info)) {
                    [$width, $height] = [(int) $size_info[0], (int) $size_info[1]];
                }
            }
        }
        return ["src" => $id, "width" => $width, "height" => $height, "alt" => "", "webp" => ""];
    }

    if (!function_exists("lsd_get_thumb")) return [];

    $img = lsd_get_thumb((int) $id, $size);
    if (empty($img[0])) return [];

    return [
        "src" => $img[0],
        "width" => $img[1] ?? 0,
        "height" => $img[2] ?? 0,
        "alt" => $img[3] ?? "",
        "webp" => function_exists("hasWebp") ? hasWebp($img) : "",
    ];
};

$desktop = !empty($raw["desktop"]) ? $resolve($raw["desktop"], $desktopSize) : [];
$mobile  = !empty($raw["mobile"]) ? $resolve($raw["mobile"], $mobileSize) : [];

$lazy = $lazyFlag ? ' loading="lazy"' : "";
$priority = $lazy ? ' fetchpriority="low"' : ' fetchpriority="high"';
$fallback = !empty($desktop) ? $desktop : $mobile;
$alt = !empty($fallback["alt"]) ? ' alt="' . $escape($fallback["alt"]) . '"' : 'alt=""';
$classes = $classesValue ? ' class="' . component::classes($classesValue) . '"' : "";
$media = $mobile ? ' media="(min-width:' . $breakpoint . 'px)"' : "";
$media_mobile = $mobile ? ' media="(max-width:' . ($breakpoint - 1) . 'px)"' : "";

$size_attr = function ($img) {
    $w = (int) ($img["width"] ?? 0);
    $h = (int) ($img["height"] ?? 0);
    if ($w <= 0 || $h <= 0) return '';
    return ' width="' . $w . '" height="' . $h . '"';
};
?>
<picture<?= $classes ?>>
    <?php if ($mobile) : ?>
        <?php if (!empty($mobile["webp"])) : ?>
            <source<?= $size_attr($mobile) ?> srcset="<?= $escape($mobile["webp"]) ?>" <?= $media_mobile ?> type="image/webp">
        <?php endif ?>
        <source<?= $size_attr($mobile) ?> srcset="<?= $escape($mobile["src"]) ?>" <?= $media_mobile ?> type="image/jpg">
    <?php endif ?>

    <?php if ($desktop) : ?>
        <?php if (!empty($desktop["webp"])) : ?>
            <source<?= $size_attr($desktop) ?> srcset="<?= $escape($desktop["webp"]) ?>" <?= $media ?> type="image/webp">
        <?php endif ?>
        <source<?= $size_attr($desktop) ?> srcset="<?= $escape($desktop["src"]) ?>" <?= $media ?> type="image/jpg">
    <?php endif ?>

    <img src="<?= $escape($fallback["src"]) ?>" <?= $alt ?><?= $size_attr($fallback) ?> <?= $lazy . $priority ?>>
</picture>
