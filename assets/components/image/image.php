<?php
/** @var array $args */
$input = $args["image"] ?? "";
$size = $args["size"] ?? "full";

if ($input === "" || $input === null) return;

$src = "";
$width = 0;
$height = 0;
$alt = "";
$webp = "";

if (is_string($input) && !is_numeric($input)) {
    // URL ou chemin direct → on tente de récupérer les dimensions réelles.
    $src = (string) $input;
    if (!preg_match('#^(https?:)?//#', $src) && $src[0] !== "/") {
        $src = dist_asset_url("img/" . $src);
    }

    if (preg_match('/(\d+)x(\d+)/', basename($src), $m)) {
        [$width, $height] = [(int) $m[1], (int) $m[2]];
    }
    if ($width === 0 || $height === 0) {
        $local_path = null;
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $root = defined("ABSPATH") ? ABSPATH : (defined("APP_ROOT") ? APP_ROOT . "/" : getcwd() . "/");
        if ($host && strpos($src, "://" . $host . "/") !== false) {
            $local_path = str_replace("https://" . $host . "/", $root, $src);
            $local_path = str_replace("http://" . $host . "/", $root, $local_path);
        } elseif (strpos($src, '/') === 0) {
            $local_path = rtrim($root, "/") . $src;
        } elseif (file_exists($src)) {
            $local_path = $src;
        }
        if ($local_path && file_exists($local_path)) {
            $info = @getimagesize($local_path);
            if (!empty($info)) {
                [$width, $height] = [(int) $info[0], (int) $info[1]];
            }
        }
    }
} else {
    // ID média WordPress.
    if (!function_exists("lsd_get_thumb")) return;

    $image = lsd_get_thumb((int) $input, $size);
    if (empty($image[0])) return;
    $src = $image[0];
    $width = (int) ($image[1] ?? 0);
    $height = (int) ($image[2] ?? 0);
    $alt = (string) ($image[3] ?? "");
    $webp = function_exists("hasWebp") ? hasWebp($image) : "";
}

if ($src === "") return;
if ($webp) $src = $webp;

$ext = strtolower((string) pathinfo(parse_url($src, PHP_URL_PATH) ?: $src, PATHINFO_EXTENSION));
$lazy = !empty($args["lazy"]) ? ' loading="lazy"' : "";
$alt_attr = $alt !== "" ? ' alt="' . esc_attr($alt) . '"' : ' alt=""';
if ($ext === "svg") $alt_attr = "";
$classes = !empty($args["classes"]) ? ' class="' . esc_attr($args["classes"]) . '"' : "";
$dims = ($width > 0 ? ' width="' . $width . '"' : '') . ($height > 0 ? ' height="' . $height . '"' : '');
?>

<img<?= $classes ?> src="<?= esc_url($src) ?>"<?= $alt_attr ?><?= $dims ?><?= $lazy ?>>
