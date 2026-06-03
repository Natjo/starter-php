<?php

/*
|--------------------------------------------------------------------------
| WordPress compatibility
|--------------------------------------------------------------------------
*/

if (!function_exists('__')) {
    function __(string $text, ?string $domain = null): string
    {
        return $text;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(mixed $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html(mixed $text): string
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(mixed $url): string
    {
        return htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_kses_post')) {
    define('STARTER_WP_KSES_POST_FALLBACK', true);

    function wp_kses_post(mixed $html): string
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

/*
|--------------------------------------------------------------------------
| Content sanitization
|--------------------------------------------------------------------------
*/

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
| Generic helpers
|--------------------------------------------------------------------------
*/

function starter_args(mixed $args, array $defaults = []): array
{
    return array_replace($defaults, is_array($args) ? $args : []);
}


/*
|--------------------------------------------------------------------------
| Template shortcuts
|--------------------------------------------------------------------------
*/

function component(string $name, array $args = []): void
{
    get_template_part("components/{$name}/{$name}", null, $args);
}

function hero(string $name, array $args = []): void
{
    get_template_part("heros/hero-{$name}/hero-{$name}", null, $args);
}

function strate(string $name, array $args = []): void
{
    $name = str_replace('-', '_', $name);
    get_template_part("strates/strate-{$name}/strate-{$name}", null, $args);
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
    $args = is_string($args) ? ['theme_location' => $args] : starter_args($args);
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

function has_unsafe_path_segments(mixed $path): bool
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

if (!function_exists('lsd_get_thumb')) {
    function lsd_get_thumb(mixed $image, mixed $size = 'full'): array
    {
        if (!is_scalar($image) || trim((string) $image) === '') {
            return [];
        }

        $source = starter_image_source($image, $size);
        if (empty($source['file']) || empty($source['root']) || empty($source['url'])) {
            return [];
        }

        $path = rtrim((string) $source['root'], '/') . '/' . $source['file'];
        if (!is_file($path)) {
            return [];
        }

        $dimensions = @getimagesize($path);
        $width = !empty($dimensions[0]) ? (int) $dimensions[0] : 0;
        $height = !empty($dimensions[1]) ? (int) $dimensions[1] : 0;

        return [$source['url'], $width, $height, ''];
    }
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

/*
|--------------------------------------------------------------------------
| Template loading
|--------------------------------------------------------------------------
*/

function get_template_part(mixed $slug, mixed $name = null, array $args = []): void
{
    if (!empty($args)) {
        $template_args = array_filter(
            $args,
            static fn($key) => is_string($key) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key),
            ARRAY_FILTER_USE_KEY
        );
        extract($template_args, EXTR_SKIP);
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

function is_safe_template_file(mixed $path, mixed $directory): bool
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

/*
|--------------------------------------------------------------------------
| Layout options and HTML attributes
|--------------------------------------------------------------------------
*/

function options(mixed $classes, array $args = []): string
{
    $options = !empty($args['options']) && is_array($args['options']) ? $args['options'] : [];

    if (empty($options)) {
        return starter_attributes(['class' => sanitize_class_list($classes)]);
    }

    $class_list = [$classes];
    $option_value = static fn($value): string => is_scalar($value) ? trim((string) $value) : '';

    $container = $option_value($options['container'] ?? '');
    if ($container !== '') {
        $class_list[] = 'ctr-' . $container;
    }

    $margin = !empty($options['margin']) && is_array($options['margin']) ? $options['margin'] : [];
    $margin_bottom = $option_value($margin['bottom'] ?? '');
    $margin_top = $option_value($margin['top'] ?? '');

    if ($margin_bottom === '') {
        $class_list[] = 'mb-0';
    } elseif ($margin_bottom !== 'md') {
        $class_list[] = 'mb-' . $margin_bottom;
    }

    if ($margin_top !== '') {
        $class_list[] = 'mt-' . $margin_top;
    }

    $background = !empty($options['background']) && is_array($options['background']) ? $options['background'] : [];
    if (!empty($background['hasbackground'])) {
        $color = $option_value($background['color'] ?? '');
        if ($color !== '') {
            $class_list[] = 'bg-' . $color;
        }

        $padding = !empty($background['padding']) && is_array($background['padding']) ? $background['padding'] : [];
        foreach (['top' => 'pt', 'bottom' => 'pb'] as $key => $prefix) {
            $value = $option_value($padding[$key] ?? '');
            if ($value !== '' && $value !== 'md') {
                $class_list[] = $prefix . '-' . $value;
            }
        }
    }

    $attributes = ['class' => sanitize_class_list($class_list)];
    if (!empty($options['id'])) {
        $attributes['id'] = sanitize_html_class($option_value($options['id']));
    }

    return starter_attributes($attributes);
}

function sanitize_class_list(mixed $classes): string
{
    if (is_array($classes)) {
        $classes = implode(' ', array_filter($classes, static fn($class) => is_scalar($class)));
    }

    $classes = preg_split('/\s+/', (string) $classes, -1, PREG_SPLIT_NO_EMPTY);
    $classes = array_map('sanitize_html_class', $classes);
    $classes = array_filter($classes, static fn($class) => $class !== '');

    return implode(' ', array_unique($classes));
}

function starter_attributes(mixed $attributes): string
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

/*
|--------------------------------------------------------------------------
| Media helpers
|--------------------------------------------------------------------------
*/

function youtube_id_from_url(mixed $url): string
{
    $parts = parse_url((string) $url);
    $id = "";

    if (isset($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (isset($qs['v'])) {
            $id = (string) $qs['v'];
        } else if (isset($qs['vi'])) {
            $id = (string) $qs['vi'];
        }
    }

    if ($id === '' && isset($parts['path'])) {
        $path = explode('/', trim($parts['path'], '/'));
        $id = (string) $path[count($path) - 1];
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : "";
}
