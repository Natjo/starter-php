<?php



/*
|--------------------------------------------------------------------------
| Content sanitization
|--------------------------------------------------------------------------
*/




function starter_sanitize_dom_attributes(DOMElement $node, array $allowed_attributes): string
{
    $attributes = '';

    foreach ($allowed_attributes as $attribute) {
        if (!$node->hasAttribute($attribute)) {
            continue;
        }

        $value = trim($node->getAttribute($attribute));
        if ($value === '') {
            continue;
        }

        if ($attribute === 'href') {
            $value = starter_safe_content_url($value);
            if ($value === '') {
                continue;
            }
        } elseif ($attribute === 'target') {
            $value = in_array($value, ['_blank', '_self', '_parent', '_top'], true) ? $value : '';
            if ($value === '') {
                continue;
            }
        } elseif ($attribute === 'rel') {
            $value = sanitize_class_list($value);
        } elseif ($attribute === 'class') {
            $value = sanitize_class_list($value);
        }

        $attributes .= ' ' . $attribute . '="' . esc_attr($value) . '"';
    }

    if ($node->hasAttribute('target') && $node->getAttribute('target') === '_blank' && !str_contains($attributes, ' rel=')) {
        $attributes .= ' rel="noopener noreferrer"';
    }

    return $attributes;
}

function starter_safe_content_url(string $url): string
{
    $url = trim($url);

    if ($url === '') {
        return '';
    }

    if (str_starts_with($url, '#')) {
        return $url;
    }

    if (str_starts_with($url, '//')) {
        return '';
    }

    if (str_starts_with($url, '/')) {
        return has_unsafe_path_segments(parse_url($url, PHP_URL_PATH) ?: '') ? '' : $url;
    }

    if (!str_contains($url, ':') && !str_contains($url, '\\') && !preg_match('/[\x00-\x1F\x7F<>"\']/', $url)) {
        return has_unsafe_path_segments(parse_url($url, PHP_URL_PATH) ?: '') ? '' : '/' . ltrim($url, '/');
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    if (in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
        return $url;
    }

    return '';
}



/*
|--------------------------------------------------------------------------
| Dist paths and URLs
|--------------------------------------------------------------------------
*/



function dist_assets_root(): string
{
    return defined('ASSETS_ROOT') ? rtrim(ASSETS_ROOT, '/') : rtrim(WEB_ROOT . '/assets', '/');
}

function dist_uploads_root(): string
{
    return defined('UPLOADS_ROOT') ? rtrim(UPLOADS_ROOT, '/') : rtrim(WEB_ROOT . '/uploads', '/');
}

function dist_asset_url(mixed $file): string
{
    $file = normalize_dist_file($file);

    return rtrim(THEME_ASSETS, '/') . '/' . $file;
}

function dist_upload_url(mixed $file): string
{
    $file = normalize_dist_file($file);
    $base = defined('THEME_UPLOADS')
        ? THEME_UPLOADS
        : rtrim(dirname(rtrim(THEME_ASSETS, '/')), '/') . '/uploads/';

    return rtrim($base, '/') . '/' . $file;
}

/*
|--------------------------------------------------------------------------
| Images
|--------------------------------------------------------------------------
*/

function starter_image_size_name(mixed $size): string
{
    if (is_array($size)) {
        $width = isset($size[0]) ? (int) $size[0] : 0;
        $height = isset($size[1]) ? (int) $size[1] : 0;

        return $width > 0 && $height > 0 ? $width . 'x' . $height : '';
    }

    $size = is_scalar($size) ? trim((string) $size) : '';

    if ($size === '' || $size === 'full') {
        return '';
    }

    return sanitize_html_class($size);
}

function starter_image_variant_file(mixed $file, mixed $size = 'full', ?string $root = null): string
{
    $file = normalize_dist_file($file);
    $size = starter_image_size_name($size);
    $root = $root !== null ? rtrim($root, '/') : dist_uploads_root();

    if ($file === '' || $size === '') {
        return $file;
    }

    $path = pathinfo($file);
    $dirname = !empty($path['dirname']) && $path['dirname'] !== '.' ? $path['dirname'] . '/' : '';
    $extension = !empty($path['extension']) ? '.' . $path['extension'] : '';
    $candidate = $dirname . $path['filename'] . '-' . $size . $extension;

    return is_file($root . '/' . $candidate) ? $candidate : $file;
}




/*
|--------------------------------------------------------------------------
| Asset versioning
|--------------------------------------------------------------------------
*/

function dist_asset_version(mixed $file): ?string
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

function dist_versioned_asset_url(mixed $file): string
{
    $url = dist_asset_url($file);
    $version = dist_asset_version($file);

    return $version ? $url . '?v=' . $version : $url;
}

function dist_scripts(): void
{
    echo '<script type="module" src="' . esc_url(dist_versioned_asset_url('app.js')) . '"></script>' . PHP_EOL;
}

/*
|--------------------------------------------------------------------------
| CSS loading
|--------------------------------------------------------------------------
*/

function dist_css_file_exists(mixed $file): bool
{
    $file = normalize_dist_file($file);

    return $file !== '' && is_file(dist_assets_root() . '/' . $file);
}

function dist_css_manifest(): array
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

function dist_style_placeholder(): void
{
    echo '<!-- DIST_PAGE_STYLES -->' . PHP_EOL;
}

function enqueue_dist_style(mixed $file): void
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

function dist_enqueued_styles(): array
{
    $styles = !empty($GLOBALS['dist_enqueued_styles']) && is_array($GLOBALS['dist_enqueued_styles'])
        ? array_keys($GLOBALS['dist_enqueued_styles'])
        : [];

    sort($styles);
    return $styles;
}



function dist_bundles_for_styles(array $styles): array
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

function dist_style_link(mixed $file): string
{
    $href = esc_url(dist_versioned_asset_url($file));
    $media = $file === 'styles/print.css' ? ' media="print"' : '';

    return '    <link rel="stylesheet" href="' . $href . '"' . $media . '>' . PHP_EOL;
}

function dist_style_preload_link(mixed $file): string
{
    $href = esc_url(dist_versioned_asset_url($file));

    return '    <link rel="preload" href="' . $href . '" as="style">' . PHP_EOL;
}

function dist_should_preload_style(mixed $file): bool
{
    return in_array($file, [
        'common.css',
        'components.css',
        'modules.css',
    ], true);
}

function dist_critical_styles(mixed $file = 'critical.css'): void
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

function dist_bundle_styles(bool $echo = true): string
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

function dist_styles(bool $echo = true): string
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

function dist_page_styles(bool $echo = true): string
{
    $html = dist_bundle_styles(false) . dist_styles(false);

    if ($echo) {
        echo $html;
    }

    return $html;
}

function render_dist_style_placeholders(mixed $html): string
{
    return str_replace('<!-- DIST_PAGE_STYLES -->', rtrim(dist_page_styles(false)), (string) $html);
}
