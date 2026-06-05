<?php

/**
 * Compatibilité WordPress pour le starter.
 *
 * Ce fichier expose les fonctions WordPress utilisées par les composants
 * et les templates afin qu'ils puissent fonctionner dans le starter PHP,
 * sans dépendre d'une installation WordPress.
 *
 * Les fonctions gardent volontairement les mêmes noms que WordPress
 * quand le composant doit rester portable entre les deux environnements.
 * Elles ne couvrent que les besoins du starter et ne doivent pas être
 * considérées comme des réimplémentations complètes de l'API WordPress.
 */

function esc_attr(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_html(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_url(mixed $url): string
{
    return htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8');
}

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
        defined('ASSETS_ROOT') ? ASSETS_ROOT : null,
    ], static fn($directory) => is_string($directory) && is_dir($directory));

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

function lsd_get_thumb(mixed $image, mixed $size = 'full'): array
{
    if (!is_scalar($image) || trim((string) $image) === '') {
        return [];
    }

    $source = image_source($image, $size);
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

function sanitize_html_class(mixed $class, $fallback = '')
{
    $class = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $class);
    $class = trim((string) $class, '-');

    return $class !== '' ? $class : (string) $fallback;
}

function wp_kses_post(mixed $html): string
{
    $html = (string) $html;
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
        $output .= sanitize_dom_node($child, $allowed_tags);
    }

    return $output;
}

function image_source(mixed $image, mixed $size = 'full'): array
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
        $candidate = starter_image_variant_file($file, $size, starter_dist_assets_root());

        return [
            'file' => $candidate,
            'root' => starter_dist_assets_root(),
            'url' => starter_dist_asset_url($candidate),
        ];
    }

    if ($isUpload) {
        $upload = starter_image_variant_file($file, $size, starter_dist_uploads_root());
        return [
            'file' => $upload,
            'root' => starter_dist_uploads_root(),
            'url' => starter_dist_upload_url($upload),
        ];
    }

    $asset = starter_image_variant_file('img/' . $file, $size, starter_dist_assets_root());
    if (is_file(starter_dist_assets_root() . '/' . $asset)) {
        return [
            'file' => $asset,
            'root' => starter_dist_assets_root(),
            'url' => starter_dist_asset_url($asset),
        ];
    }

    return [
        'file' => $asset,
        'root' => starter_dist_assets_root(),
        'url' => starter_dist_asset_url($asset),
    ];
}

function enqueue_template_style(mixed $template): void
{
    $template = ltrim(str_replace('\\', '/', (string) $template), '/');
    $template = preg_replace('#\.php$#', '', $template);
    $basename = basename($template);

    if ($basename === '') {
        return;
    }

    starter_enqueue_dist_style($template . '.css');

    if (!str_contains($template, '/')) {
        starter_enqueue_dist_style($template . '/' . $basename . '.css');
        starter_enqueue_dist_style('common/' . $template . '/' . $basename . '.css');
    }
}

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

function sanitize_dom_node(DOMNode $node, array $allowed_tags): string
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
        $children .= sanitize_dom_node($child, $allowed_tags);
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
