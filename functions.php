<?php

if (!function_exists('__')) {
    function __($text, $domain = null)
    {
        return $text;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url)
    {
        return htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($html)
    {
        return (string) $html;
    }
}

if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($class, $fallback = '')
    {
        $class = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $class);
        $class = trim((string) $class, '-');

        return $class !== '' ? $class : (string) $fallback;
    }
}

function component($name, array $args = [])
{
    get_template_part("assets/components/{$name}/{$name}", null, $args);
}

function hero($name, array $args = [])
{
    get_template_part("assets/heros/hero-{$name}/hero-{$name}", null, $args);
}

function strate($name, array $args = [])
{
    $name = str_replace('-', '_', $name);
    get_template_part("assets/strates/strate-{$name}/strate-{$name}", null, $args);
}



function dist_asset_url($file)
{
    return rtrim(THEME_ASSETS, '/') . '/' . ltrim(str_replace('\\', '/', $file), '/');
}

function dist_files($extension, array $roots = [])
{
    $files = [];
    $base = defined('ASSETS_ROOT') ? rtrim(ASSETS_ROOT, '/') : rtrim(WEB_ROOT . '/assets', '/');
    $scanRoots = $roots ?: [''];

    foreach ($scanRoots as $root) {
        $directory = $base . ($root ? '/' . trim($root, '/') : '');

        if (!is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== ltrim($extension, '.')) {
                continue;
            }

            $files[] = str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
        }
    }

    sort($files);
    return $files;
}

function dist_styles()
{
    foreach (dist_files('css') as $file) {
        if ($file === 'styles.css' || (str_starts_with($file, 'styles/') && $file !== 'styles/print.css')) {
            continue;
        }

        $href = htmlspecialchars(dist_asset_url($file), ENT_QUOTES, 'UTF-8');
        $media = $file === 'styles/print.css' ? ' media="print"' : '';

        echo '    <link rel="stylesheet" href="' . $href . '"' . $media . '>' . PHP_EOL;
    }
}

function dist_scripts()
{
    foreach (dist_files('js') as $file) {
        if (
            $file === 'app.js'
            || str_starts_with($file, 'plugins/')
            || str_starts_with($file, 'common/')
            || str_starts_with($file, 'components/')
            || str_starts_with($file, 'heros/')
            || str_starts_with($file, 'strates/')
        ) {
            continue;
        }

        echo '    <script type="module" src="' . htmlspecialchars(dist_asset_url($file), ENT_QUOTES, 'UTF-8') . '"></script>' . PHP_EOL;
    }
}

function get_template_part($slug, $name = null, array $args = [])
{
    if (!empty($args)) {
        extract($args, EXTR_SKIP);
    }

    $slug = trim($slug, '/');
    $slug = preg_replace('#\.php$#', '', $slug);

    $templates = [];

    if ($name !== null) {
        $templates[] = "{$slug}-{$name}.php";
    }

    $templates[] = "{$slug}.php";

    $directories = [
        APP_ROOT,
        APP_ROOT . '/assets',
        WEB_ROOT,
    ];

    foreach ($templates as $template) {

        foreach ($directories as $directory) {

            $path = $directory . '/' . $template;

            if (is_file($path)) {
                include $path;
                return;
            }
        }
    }

    trigger_error("Template introuvable : {$slug}", E_USER_WARNING);
}

function options($classes, $args = [])
{
    if (empty($args["options"])) {
        return 'class="' . $classes . '"';
    } else {
        $options = $args["options"];

        $container = "";
        if (!empty($options["container"])) {
            $container = !empty($options["container"]) ? " ctr-" . $options["container"] : "";
        }

        $marginBottom = " mb-0";
        if (!empty($options["margin"]["bottom"])) {
            if ($options["margin"]["bottom"] == "md") {
                $marginBottom = "";
            } else {
                $marginBottom = !empty($options["margin"]["bottom"]) ? " mb-" . $options["margin"]["bottom"] : "";
            }
        }

        $marginTop = "";
        if (!empty($options["margin"]["top"])) {
            $marginTop = !empty($options["margin"]["top"]) ? " mt-" . $options["margin"]["top"] : "";
        }

        $background = "";
        $paddingTop = "";
        $paddingBottom = "";
        if (!empty($options["background"]["hasbackground"])) {
            $background = " bg-" . $options["background"]["color"];
            $padding = $options["background"]["padding"];

            if ($padding["top"] == "md" || empty($padding["top"])) {
                $paddingTop = "";
            } else {
                $paddingTop =   " pt-" . $padding["top"];
            }

            if ($padding["bottom"] == "md" || empty($padding["bottom"])) {
                $paddingBottom = "";
            } else {
                $paddingBottom =   " pb-" . $padding["bottom"];
            }
        }

        $id = "";
        if (!empty($options["id"])) {
            $id = ' id="' . $options["id"] . '"';
        }

        return 'class="' . $classes . $marginBottom . $marginTop . $background . $paddingTop . $paddingBottom . $container . '"' . $id;
    }
}

function youtube_id_from_url($url)
{
    $parts = parse_url($url);

    if (isset($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (isset($qs['v'])) {
            return $qs['v'];
        } else if (isset($qs['vi'])) {
            return $qs['vi'];
        }
    }

    if (isset($parts['path'])) {
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path) - 1];
    }

    return "";
}
