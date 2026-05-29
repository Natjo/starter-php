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
    define('STARTER_WP_KSES_POST_FALLBACK', true);

    function wp_kses_post($html)
    {
        return (string) $html;
    }
}

function starter_kses_post($html)
{
    if (function_exists('wp_kses') && function_exists('wp_kses_allowed_html')) {
        return wp_kses((string) $html, wp_kses_allowed_html('post'));
    }

    if (function_exists('wp_kses_post') && !defined('STARTER_WP_KSES_POST_FALLBACK')) {
        return wp_kses_post($html);
    }

    return strip_tags((string) $html, '<br><strong><b><em><i><span><sup><sub>');
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
    get_template_part("components/{$name}/{$name}", null, $args);
}

function hero($name, array $args = [])
{
    get_template_part("heros/hero-{$name}/hero-{$name}", null, $args);
}

function strate($name, array $args = [])
{
    $name = str_replace('-', '_', $name);
    get_template_part("strates/strate-{$name}/strate-{$name}", null, $args);
}

function normalize_dist_file($file)
{
    $file = ltrim(str_replace('\\', '/', (string) $file), '/');
    $file = preg_replace('#/+#', '/', $file);

    if ($file === '' || has_unsafe_path_segments($file)) {
        return '';
    }

    return $file;
}

function has_unsafe_path_segments($path)
{
    if (str_contains((string) $path, "\0")) {
        return true;
    }

    foreach (explode('/', (string) $path) as $segment) {
        if ($segment === '..') {
            return true;
        }
    }

    return false;
}

function dist_assets_root()
{
    return defined('ASSETS_ROOT') ? rtrim(ASSETS_ROOT, '/') : rtrim(WEB_ROOT . '/assets', '/');
}

function dist_asset_url($file)
{
    $file = normalize_dist_file($file);

    return rtrim(THEME_ASSETS, '/') . '/' . $file;
}

function dist_asset_version($file)
{
    static $versions = [];

    $file = normalize_dist_file($file);

    if ($file === '') {
        return null;
    }

    if (array_key_exists($file, $versions)) {
        return $versions[$file];
    }

    $path = dist_assets_root() . '/' . $file;
    $versions[$file] = is_file($path) ? substr(md5_file($path), 0, 10) : null;

    return $versions[$file];
}

function dist_versioned_asset_url($file)
{
    $url = dist_asset_url($file);
    $version = dist_asset_version($file);

    return $version ? $url . '?v=' . $version : $url;
}

function dist_css_file_exists($file)
{
    $file = normalize_dist_file($file);

    return $file !== '' && is_file(dist_assets_root() . '/' . $file);
}

function dist_css_manifest()
{
    static $manifest = null;

    if ($manifest !== null) {
        return $manifest;
    }

    $path = dist_assets_root() . '/css-bundles.json';

    if (!is_file($path)) {
        $manifest = [
            'bundles' => [],
            'bundledFiles' => [],
            'fileBundles' => [],
        ];
        return $manifest;
    }

    $data = json_decode((string) file_get_contents($path), true);
    $manifest = is_array($data) ? $data : [];
    $manifest['bundles'] = !empty($manifest['bundles']) && is_array($manifest['bundles']) ? $manifest['bundles'] : [];
    $manifest['bundledFiles'] = !empty($manifest['bundledFiles']) && is_array($manifest['bundledFiles']) ? $manifest['bundledFiles'] : [];
    $manifest['fileBundles'] = !empty($manifest['fileBundles']) && is_array($manifest['fileBundles']) ? $manifest['fileBundles'] : [];

    return $manifest;
}

function dist_style_placeholder()
{
    echo '<!-- DIST_PAGE_STYLES -->' . PHP_EOL;
}

function enqueue_dist_style($file)
{
    $file = ltrim(str_replace('\\', '/', (string) $file), '/');
    $manifest = dist_css_manifest();
    $bundledFiles = array_flip($manifest['bundledFiles']);

    if ($file === '' || (!dist_css_file_exists($file) && !isset($bundledFiles[$file]))) {
        return;
    }

    if (empty($GLOBALS['dist_enqueued_styles']) || !is_array($GLOBALS['dist_enqueued_styles'])) {
        $GLOBALS['dist_enqueued_styles'] = [];
    }

    $GLOBALS['dist_enqueued_styles'][$file] = true;
}

function dist_enqueued_styles()
{
    $styles = !empty($GLOBALS['dist_enqueued_styles']) && is_array($GLOBALS['dist_enqueued_styles'])
        ? array_keys($GLOBALS['dist_enqueued_styles'])
        : [];

    sort($styles);
    return $styles;
}

function enqueue_template_style($template)
{
    $template = ltrim(str_replace('\\', '/', (string) $template), '/');
    $template = preg_replace('#\.php$#', '', $template);
    $basename = basename($template);

    if ($basename === '') {
        return;
    }

    enqueue_dist_style($template . '.css');

    if (!str_contains($template, '/')) {
        enqueue_dist_style($template . '/' . $basename . '.css');
        enqueue_dist_style('common/' . $template . '/' . $basename . '.css');
    }
}

function dist_bundles_for_styles(array $styles)
{
    $manifest = dist_css_manifest();
    $bundledFiles = array_flip($manifest['bundledFiles']);
    $bundles = [];

    foreach ($styles as $file) {
        if (!isset($bundledFiles[$file])) {
            continue;
        }

        $group = $manifest['fileBundles'][$file] ?? strtok($file, '/');

        if ($group === 'vendors') {
            $group = 'common';
        }

        if (!is_string($group) || empty($manifest['bundles'][$group])) {
            continue;
        }

        $bundle = $manifest['bundles'][$group];

        if (dist_css_file_exists($bundle)) {
            $bundles[$bundle] = true;
        }
    }

    return array_keys($bundles);
}

function dist_style_link($file)
{
    $href = esc_url(dist_versioned_asset_url($file));
    $media = $file === 'styles/print.css' ? ' media="print"' : '';

    return '    <link rel="stylesheet" href="' . $href . '"' . $media . '>' . PHP_EOL;
}

function dist_style_preload_link($file)
{
    $href = esc_url(dist_versioned_asset_url($file));

    return '    <link rel="preload" href="' . $href . '" as="style">' . PHP_EOL;
}

function dist_should_preload_style($file)
{
    return in_array($file, [
        'common.css',
        'components.css',
        'modules.css',
    ], true);
}

function dist_critical_styles($file = 'critical.css')
{
    $file = normalize_dist_file($file);
    $path = dist_assets_root() . '/' . $file;

    if ($file === '' || !is_file($path)) {
        return;
    }

    $css = file_get_contents($path);
    $css = preg_replace('~/\*# sourceMappingURL=[^*]+\*/~', '', $css);
    $css = preg_replace_callback('~url\((["\']?)(?!data:|https?:|//|#)([^"\')]+)\1\)~i', function ($matches) {
        $url = ltrim($matches[2], './');
        return 'url("' . esc_url(dist_asset_url($url)) . '")';
    }, $css);

    echo '    <style id="critical-css">' . $css . '</style>' . PHP_EOL;
}

function dist_bundle_styles($echo = true)
{
    $html = '';

    foreach (dist_bundles_for_styles(dist_enqueued_styles()) as $file) {
        if (dist_should_preload_style($file)) {
            $html .= dist_style_preload_link($file);
        }

        $html .= dist_style_link($file);
    }

    if ($echo) {
        echo $html;
    }

    return $html;
}

function dist_styles($echo = true)
{
    $manifest = dist_css_manifest();
    $bundled = array_merge([
        'critical.css',
        'styles.css',
    ], array_values($manifest['bundles']));
    $bundledFiles = array_flip($manifest['bundledFiles']);

    $styles = array_unique(dist_enqueued_styles());

    sort($styles);
    $html = '';

    foreach ($styles as $file) {
        if (
            in_array($file, $bundled, true)
            || isset($bundledFiles[$file])
            || (str_starts_with($file, 'styles/') && $file !== 'styles/print.css')
            || !dist_css_file_exists($file)
        ) {
            continue;
        }

        $html .= dist_style_link($file);
    }

    if ($echo) {
        echo $html;
    }

    return $html;
}

function dist_page_styles($echo = true)
{
    $html = dist_bundle_styles(false) . dist_styles(false);

    if ($echo) {
        echo $html;
    }

    return $html;
}

function render_dist_style_placeholders($html)
{
    return str_replace('<!-- DIST_PAGE_STYLES -->', rtrim(dist_page_styles(false)), $html);
}

function get_template_part($slug, $name = null, array $args = [])
{
    if (!empty($args)) {
        extract($args, EXTR_SKIP);
    }

    $slug = normalize_template_slug($slug);

    if ($slug === '') {
        trigger_error('Template introuvable : slug vide', E_USER_WARNING);
        return;
    }

    $slug = preg_replace('#\.php$#', '', $slug);

    $slugs = [$slug];
    $basename = basename($slug);

    if (!str_contains($slug, '/')) {
        $slugs[] = "{$slug}/{$basename}";
    }

    if (!str_starts_with($slug, 'common/')) {
        $slugs[] = 'common/' . $slug;

        if (!str_contains($slug, '/')) {
            $slugs[] = "common/{$slug}/{$basename}";
        }
    }

    $slugs = array_values(array_unique($slugs));

    $templates = [];

    foreach ($slugs as $template_slug) {
        if ($name !== null) {
            $templates[] = "{$template_slug}-{$name}.php";
        }

        $templates[] = "{$template_slug}.php";
    }

    $directories = array_filter([
        APP_ROOT,
        APP_ROOT . '/assets',
        WEB_ROOT,
    ], static fn($directory) => is_dir($directory));

    foreach ($templates as $template) {

        foreach ($directories as $directory) {

            $path = $directory . '/' . $template;

            if (is_safe_template_file($path, $directory)) {
                enqueue_template_style($template);
                include $path;
                return;
            }
        }
    }

    trigger_error("Template introuvable : {$slug}", E_USER_WARNING);
}

function normalize_template_slug($slug)
{
    $slug = trim(str_replace('\\', '/', (string) $slug), '/');
    $slug = preg_replace('#\.php$#', '', $slug);
    $slug = preg_replace('#/+#', '/', $slug);

    if ($slug === '' || has_unsafe_path_segments($slug)) {
        return '';
    }

    return preg_match('#^[A-Za-z0-9_/-]+$#', $slug) ? $slug : '';
}

function is_safe_template_file($path, $directory)
{
    if (!is_file($path)) {
        return false;
    }

    $realPath = realpath($path);
    $realDirectory = realpath($directory);

    return $realPath !== false
        && $realDirectory !== false
        && str_starts_with($realPath, rtrim($realDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

function options($classes, $args = [])
{
    $classes = sanitize_class_list($classes);

    if (empty($args["options"])) {
        return 'class="' . esc_attr($classes) . '"';
    } else {
        $options = $args["options"];

        $container = "";
        if (!empty($options["container"])) {
            $container = !empty($options["container"]) ? " ctr-" . sanitize_html_class($options["container"]) : "";
        }

        $marginBottom = " mb-0";
        if (!empty($options["margin"]["bottom"])) {
            if ($options["margin"]["bottom"] == "md") {
                $marginBottom = "";
            } else {
                $marginBottom = !empty($options["margin"]["bottom"]) ? " mb-" . sanitize_html_class($options["margin"]["bottom"]) : "";
            }
        }

        $marginTop = "";
        if (!empty($options["margin"]["top"])) {
            $marginTop = !empty($options["margin"]["top"]) ? " mt-" . sanitize_html_class($options["margin"]["top"]) : "";
        }

        $background = "";
        $paddingTop = "";
        $paddingBottom = "";
        if (!empty($options["background"]["hasbackground"])) {
            $color = sanitize_html_class($options["background"]["color"] ?? '');
            $background = $color !== '' ? " bg-" . $color : '';
            $padding = !empty($options["background"]["padding"]) && is_array($options["background"]["padding"])
                ? $options["background"]["padding"]
                : [];

            if (($padding["top"] ?? '') == "md" || empty($padding["top"])) {
                $paddingTop = "";
            } else {
                $paddingTop = " pt-" . sanitize_html_class($padding["top"]);
            }

            if (($padding["bottom"] ?? '') == "md" || empty($padding["bottom"])) {
                $paddingBottom = "";
            } else {
                $paddingBottom = " pb-" . sanitize_html_class($padding["bottom"]);
            }
        }

        $id = "";
        if (!empty($options["id"])) {
            $id = ' id="' . esc_attr(sanitize_html_class($options["id"])) . '"';
        }

        return 'class="' . esc_attr(trim($classes . $marginBottom . $marginTop . $background . $paddingTop . $paddingBottom . $container)) . '"' . $id;
    }
}

function sanitize_class_list($classes)
{
    if (is_array($classes)) {
        $classes = implode(' ', array_filter($classes, static fn($class) => is_scalar($class)));
    }

    $classes = preg_split('/\s+/', (string) $classes, -1, PREG_SPLIT_NO_EMPTY);
    $classes = array_map('sanitize_html_class', $classes);
    $classes = array_filter($classes, static fn($class) => $class !== '');

    return implode(' ', array_unique($classes));
}

function starter_attributes($attributes)
{
    if (is_string($attributes)) {
        return trim($attributes);
    }

    if (!is_array($attributes)) {
        return '';
    }

    $html = [];

    foreach ($attributes as $name => $value) {
        $name = strtolower((string) $name);

        if (!preg_match('/^[a-z][a-z0-9_:-]*$/', $name) || $value === null || $value === false) {
            continue;
        }

        if ($value === true) {
            $html[] = esc_attr($name);
            continue;
        }

        $html[] = esc_attr($name) . '="' . esc_attr($value) . '"';
    }

    return implode(' ', $html);
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
