<?php
function normalize_args(mixed $args, array $defaults = []): array
{
    return array_replace($defaults, is_array($args) ? $args : []);
}

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

function sanitize_html_class(mixed $class, $fallback = '')
{
    $class = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $class);
    $class = trim((string) $class, '-');

    return $class !== '' ? $class : (string) $fallback;
}


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



/**
 * 
 * pas dans wp mais utliser dans les fakes functions
 * 
 */

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
