<?php

/*
|--------------------------------------------------------------------------
| WordPress compatibility
|--------------------------------------------------------------------------
*/


if (!function_exists('wp_kses_post')) {
    define('STARTER_WP_KSES_POST_FALLBACK', true);

    function wp_kses_post(mixed $html): string
    {
        return (string) $html;
    }
}


function starter_kses_post(mixed $html): string
{
    if (function_exists('wp_kses') && function_exists('wp_kses_allowed_html')) {
        return wp_kses((string) $html, wp_kses_allowed_html('post'));
    }

    if (function_exists('wp_kses_post') && !defined('STARTER_WP_KSES_POST_FALLBACK')) {
        return wp_kses_post($html);
    }

    return starter_kses_post_fallback((string) $html);
}

function starter_kses_post_fallback(string $html): string
{
    $allowed_tags = [
        'a' => ['href', 'target', 'rel', 'title', 'class'],
        'blockquote' => ['class'],
        'br' => [],
        'b' => ['class'],
        'em' => ['class'],
        'h2' => ['class'],
        'h3' => ['class'],
        'h4' => ['class'],
        'h5' => ['class'],
        'h6' => ['class'],
        'i' => ['class'],
        'li' => ['class'],
        'ol' => ['class'],
        'p' => ['class'],
        'small' => ['class'],
        'span' => ['class'],
        'strong' => ['class'],
        'sub' => ['class'],
        'sup' => ['class'],
        'ul' => ['class'],
    ];

    $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#isu', '', $html) ?? '';

    if (!class_exists('DOMDocument')) {
        $html = preg_replace('/<([a-z][a-z0-9]*)\b[^>]*>/iu', '<$1>', $html) ?? '';
        return strip_tags($html, '<a><blockquote><br><b><em><h2><h3><h4><h5><h6><i><li><ol><p><small><span><strong><sub><sup><ul>');
    }

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = $dom->documentElement;
    if (!$root) {
        return '';
    }

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= starter_sanitize_dom_node($child, $allowed_tags);
    }

    return $output;
}




/*
|--------------------------------------------------------------------------
| Content sanitization
|--------------------------------------------------------------------------
*/


function starter_sanitize_dom_node(DOMNode $node, array $allowed_tags): string
{
    if ($node instanceof DOMText) {
        return htmlspecialchars($node->wholeText, ENT_QUOTES, 'UTF-8');
    }

    if (!$node instanceof DOMElement) {
        return '';
    }

    $tag = strtolower($node->tagName);
    if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
        return '';
    }

    $children = '';

    foreach ($node->childNodes as $child) {
        $children .= starter_sanitize_dom_node($child, $allowed_tags);
    }

    if (!isset($allowed_tags[$tag])) {
        return $children;
    }

    $attributes = starter_sanitize_dom_attributes($node, $allowed_tags[$tag]);

    if ($tag === 'br') {
        return '<br>';
    }

    return '<' . $tag . $attributes . '>' . $children . '</' . $tag . '>';
}

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
| Menu
|--------------------------------------------------------------------------
*/

function starter_menus_file(): string
{
    return APP_ROOT . '/menus.json';
}

function starter_menu_location(mixed $location): string
{
    $location = is_scalar($location) ? trim((string) $location) : '';

    return $location !== '' ? sanitize_html_class($location) : '';
}

function starter_menus(): array
{
    static $menus = null;

    if ($menus !== null) {
        return $menus;
    }

    $file = starter_menus_file();
    if (!is_file($file)) {
        $menus = [];
        return $menus;
    }

    $data = json_decode((string) file_get_contents($file), true);
    $items = is_array($data['menus'] ?? null) ? $data['menus'] : [];
    $menus = [];

    foreach ($items as $menu) {
        if (!is_array($menu)) {
            continue;
        }

        $location = starter_menu_location($menu['theme_location'] ?? '');
        if ($location === '') {
            continue;
        }

        $title = is_scalar($menu['title'] ?? null) ? trim((string) $menu['title']) : $location;
        $links = [];

        foreach (($menu['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $item_title = is_scalar($item['title'] ?? null) ? trim((string) $item['title']) : '';
            $item_url = is_scalar($item['url'] ?? null) ? trim((string) $item['url']) : '';

            if ($item_title === '' || $item_url === '') {
                continue;
            }

            $links[] = [
                'title' => $item_title,
                'url' => starter_safe_content_url($item_url),
            ];
        }

        $menus[$location] = [
            'title' => $title !== '' ? $title : $location,
            'theme_location' => $location,
            'items' => array_values(array_filter($links, static fn($item) => $item['url'] !== '')),
        ];
    }

    return $menus;
}

function nav_menu(mixed $args = []): void
{
    $args = is_string($args) ? ['theme_location' => $args] : normalize_args($args);
    $location = starter_menu_location($args['theme_location'] ?? '');

    if ($location === '') {
        return;
    }

    $menu = starter_menus()[$location] ?? null;
    if (empty($menu['items']) || !is_array($menu['items'])) {
        return;
    }

    $current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
    $nav_class = sanitize_class_list($args['container_class'] ?? ['nav-menu', 'nav-menu-' . $location]);
    $list_class = sanitize_class_list($args['menu_class'] ?? 'nav-menu-list');
    $item_class = sanitize_class_list($args['item_class'] ?? 'nav-menu-item');
    $link_class = sanitize_class_list($args['link_class'] ?? 'nav-menu-link');
    $aria_label = is_scalar($args['label'] ?? null) && trim((string) $args['label']) !== ''
        ? trim((string) $args['label'])
        : (string) $menu['title'];
    ?>
    <nav class="<?= esc_attr($nav_class) ?>" aria-label="<?= esc_attr($aria_label) ?>">
        <ul class="<?= esc_attr($list_class) ?>">
            <?php foreach ($menu['items'] as $item) :
                $url = (string) $item['url'];
                $title = (string) $item['title'];
                $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
                $is_active = $path === $current;
            ?>
                <li class="<?= esc_attr($item_class) ?>">
                    <a class="<?= esc_attr($link_class) ?>" href="<?= esc_url($url) ?>"<?= $is_active ? ' aria-current="page"' : '' ?>><?= esc_html($title) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <?php
}

/*
|--------------------------------------------------------------------------
| Dist paths and URLs
|--------------------------------------------------------------------------
*/

function normalize_dist_file(mixed $file): string
{
    $file = ltrim(str_replace('\\', '/', (string) $file), '/');
    $file = preg_replace('#/+#', '/', $file);
    $file = preg_replace('#^\./+#', '', $file);

    if ($file === '' || has_unsafe_path_segments($file)) {
        return '';
    }

    return $file;
}

function normalize_template_slug(mixed $slug): string
{
    $slug = trim(str_replace('\\', '/', (string) $slug), '/');
    $slug = preg_replace('#\.php$#', '', $slug);
    $slug = preg_replace('#/+#', '/', $slug);

    if ($slug === '' || has_unsafe_path_segments($slug)) {
        return '';
    }

    return preg_match('#^[A-Za-z0-9_/-]+$#', $slug) ? $slug : '';
}


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

function starter_image_source(mixed $image, mixed $size = 'full'): array
{
    $file = normalize_dist_file($image);
    $isUpload = false;

    if (defined('THEME_UPLOADS')) {
        $themeUploads = rtrim(normalize_dist_file(THEME_UPLOADS), '/');
        if ($themeUploads !== '' && str_starts_with($file, $themeUploads . '/')) {
            $file = substr($file, strlen($themeUploads) + 1);
            $isUpload = true;
        }
    }

    if (defined('THEME_ASSETS')) {
        $themeAssets = rtrim(normalize_dist_file(THEME_ASSETS), '/');
        if ($themeAssets !== '' && str_starts_with($file, $themeAssets . '/')) {
            $file = substr($file, strlen($themeAssets) + 1);
        }
    }

    if (str_starts_with($file, 'dist/uploads/')) {
        $file = substr($file, strlen('dist/uploads/'));
        $isUpload = true;
    }

    if (str_starts_with($file, 'dist/assets/')) {
        $file = substr($file, strlen('dist/assets/'));
    }

    if ($file === '') {
        return [];
    }

    if (str_starts_with($file, 'uploads/')) {
        $file = substr($file, strlen('uploads/'));
        $isUpload = true;
    }

    if (str_starts_with($file, 'img/')) {
        $candidate = starter_image_variant_file($file, $size, dist_assets_root());

        return [
            'file' => $candidate,
            'root' => dist_assets_root(),
            'url' => dist_asset_url($candidate),
        ];
    }

    if ($isUpload) {
        $upload = starter_image_variant_file($file, $size, dist_uploads_root());
        return [
            'file' => $upload,
            'root' => dist_uploads_root(),
            'url' => dist_upload_url($upload),
        ];
    }

    $asset = starter_image_variant_file('img/' . $file, $size, dist_assets_root());
    if (is_file(dist_assets_root() . '/' . $asset)) {
        return [
            'file' => $asset,
            'root' => dist_assets_root(),
            'url' => dist_asset_url($asset),
        ];
    }

    return [
        'file' => $asset,
        'root' => dist_assets_root(),
        'url' => dist_asset_url($asset),
    ];
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

function enqueue_template_style(mixed $template): void
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
