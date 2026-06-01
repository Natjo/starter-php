<?php
/** @var array $args */
$args = starter_args($args ?? null);
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

if (!function_exists("starter_picture_size_name")) {
    function starter_picture_size_name(mixed $size): string
    {
        if (is_array($size)) {
            $width = (int) ($size[0] ?? 0);
            $height = (int) ($size[1] ?? 0);

            return $width > 0 && $height > 0 ? $width . "x" . $height : "";
        }

        $size = is_scalar($size) ? trim((string) $size) : "";

        return $size !== "" && $size !== "full" ? $size : "";
    }
}

if (!function_exists("starter_picture_upload_relative_file")) {
    function starter_picture_upload_relative_file(mixed $src): string
    {
        if (!defined("THEME_UPLOADS")) {
            return "";
        }

        $src = str_replace("\\", "/", (string) $src);
        $uploads = rtrim(str_replace("\\", "/", THEME_UPLOADS), "/") . "/";

        if (!str_starts_with($src, $uploads)) {
            return "";
        }

        $relative = ltrim(substr($src, strlen($uploads)), "/");

        return preg_match('#(^|/)\.\.(/|$)#', $relative) ? "" : $relative;
    }
}

if (!function_exists("starter_picture_upload_file_path")) {
    function starter_picture_upload_file_path(mixed $src): string
    {
        if (!defined("UPLOADS_ROOT")) {
            return "";
        }

        $relative = starter_picture_upload_relative_file($src);

        return $relative !== "" ? rtrim(UPLOADS_ROOT, "/") . "/" . $relative : "";
    }
}

if (!function_exists("starter_picture_webp_src")) {
    function starter_picture_webp_src(mixed $src, mixed $size): string
    {
        static $cache = [];

        if (!defined("UPLOADS_ROOT") || !defined("THEME_UPLOADS")) {
            return "";
        }

        $key = (string) $src . '|' . (is_array($size) ? implode('x', $size) : (string) $size);
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $relative = starter_picture_upload_relative_file($src);
        if ($relative === "") {
            return $cache[$key] = "";
        }

        $path = pathinfo($relative);
        $dirname = !empty($path["dirname"]) && $path["dirname"] !== "." ? $path["dirname"] . "/" : "";
        $suffix = starter_picture_size_name($size);
        $suffix = $suffix !== "" ? "-" . $suffix : "";
        $webp = $dirname . $path["filename"] . $suffix . ".webp";

        if (!is_file(rtrim(UPLOADS_ROOT, "/") . "/" . $webp)) {
            return $cache[$key] = "";
        }

        return $cache[$key] = rtrim(THEME_UPLOADS, "/") . "/" . $webp;
    }
}

if (!function_exists("starter_picture_upload_dimensions")) {
    function starter_picture_upload_dimensions(mixed $src): array
    {
        static $cache = [];

        $path = starter_picture_upload_file_path($src);
        if ($path === "") {
            return [];
        }

        if (array_key_exists($path, $cache)) {
            return $cache[$path];
        }

        if (!is_file($path)) {
            return $cache[$path] = [];
        }

        $dimensions = @getimagesize($path);

        return $cache[$path] = [
            "width" => (int) ($dimensions[0] ?? 0),
            "height" => (int) ($dimensions[1] ?? 0),
        ];
    }
}

if (!function_exists("starter_picture_mime_type")) {
    function starter_picture_mime_type(mixed $src): string
    {
        return match (strtolower(pathinfo((string) $src, PATHINFO_EXTENSION))) {
            "png" => "image/png",
            "webp" => "image/webp",
            "gif" => "image/gif",
            default => "image/jpeg",
        };
    }
}

if (empty($raw["desktop"]) && empty($raw["mobile"])) {
    if ($placeholder) {
        echo '<picture class="' . component::classes("placeholder", $classesValue) . '"></picture>';
    }
    return;
}

$resolve = function ($id, $size) {
    $img = lsd_get_thumb($id, $size);
    if (empty($img[0])) return [];
    $webp = starter_picture_webp_src($img[0], $size);
    $webpDimensions = $webp !== "" ? starter_picture_upload_dimensions($webp) : [];

    return [
        "src" => $img[0],
        "width" => $img[1] ?? 0,
        "height" => $img[2] ?? 0,
        "alt" => $img[3] ?? "",
        "webp" => $webp,
        "webp_width" => $webpDimensions["width"] ?? 0,
        "webp_height" => $webpDimensions["height"] ?? 0,
        "type" => starter_picture_mime_type($img[0]),
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

$webp_size_attr = function ($img) {
    $w = (int) ($img["webp_width"] ?? 0);
    $h = (int) ($img["webp_height"] ?? 0);
    if ($w <= 0 || $h <= 0) return '';
    return ' width="' . $w . '" height="' . $h . '"';
};
?>
<picture<?= $classes ?>>
    <?php if ($mobile) : ?>
        <?php if (!empty($mobile["webp"])) : ?>
            <source<?= $webp_size_attr($mobile) ?> srcset="<?= $escape($mobile["webp"]) ?>" <?= $media_mobile ?> type="image/webp">
        <?php endif ?>
        <source<?= $size_attr($mobile) ?> srcset="<?= $escape($mobile["src"]) ?>" <?= $media_mobile ?> type="<?= $escape($mobile["type"]) ?>">
    <?php endif ?>

    <?php if ($desktop) : ?>
        <?php if (!empty($desktop["webp"])) : ?>
            <source<?= $webp_size_attr($desktop) ?> srcset="<?= $escape($desktop["webp"]) ?>" <?= $media ?> type="image/webp">
        <?php endif ?>
        <?php if ($mobile) : ?>
            <source<?= $size_attr($desktop) ?> srcset="<?= $escape($desktop["src"]) ?>" <?= $media ?> type="<?= $escape($desktop["type"]) ?>">
        <?php endif ?>
    <?php endif ?>

    <img src="<?= $escape($fallback["src"]) ?>" <?= $alt ?><?= $size_attr($fallback) ?> <?= $lazy . $priority ?>>
</picture>
