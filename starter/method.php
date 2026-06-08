<?php

/**
 * Helpers communs aux templates et composants.
 *
 * Ce fichier regroupe les fonctions utilitaires génériques du starter:
 * normalisation d'arguments, sécurisation de slugs, génération d'attributs
 * HTML et raccourcis de rendu comme component(), common(), hero(),
 * strate() ou card().
 *
 * Ces helpers restent volontairement indépendants de WordPress afin de
 * pouvoir être repris dans un thème ou dans le starter sans changer les
 * composants qui les utilisent.
 */

function normalize_args(mixed $args, array $defaults = []): array
{
    return array_replace($defaults, is_array($args) ? $args : []);
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

function options(mixed $classes, array $args = []): string
{
    $options = !empty($args['options']) && is_array($args['options']) ? $args['options'] : [];

    if (empty($options)) {
        return html_attributes(['class' => sanitize_class_list($classes)]);
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

    return html_attributes($attributes);
}

function component(string $name, array $args = []): void
{
    get_template_part("components/{$name}/{$name}", null, $args);
}

function common(string $name, array $args = []): void
{
    $name = trim($name);
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;

    get_template_part("common/{$name}/{$name}", null, $args);
}

function hero(string $name, array $args = []): void
{
    $template = 'heros/hero-' . $name . '/hero-' . $name . '.css';
    starter_enqueue_critical_style($template);
    get_template_part("heros/hero-{$name}/hero-{$name}", null, $args);
}

function strate(string $name, array $args = []): void
{
    $name = str_replace('-', '_', trim($name));

    if ($name === '' || !preg_match('/^[A-Za-z0-9_]+$/', $name)) return;

    $template = "strates/strate-{$name}/strate-{$name}";
    $style = $template . '.css';
    $manifest = starter_dist_css_manifest();
    $is_bundled = in_array($style, $manifest['bundledFiles'], true);
    $was_enqueued = !empty($GLOBALS['starter_dist_enqueued_styles'][$style]);

    ob_start();
    get_template_part($template, null, $args);
    $html = (string) ob_get_clean();

    if (trim($html) === '') return;

    $is_first = empty($GLOBALS['starter_rendered_strate_count']);

    if (!$is_bundled && !$was_enqueued) {
        unset($GLOBALS['starter_dist_enqueued_styles'][$style]);
    }

    if ($is_first) {
        starter_enqueue_dist_style($style);
    } elseif (
        !$is_bundled
        && !$was_enqueued
        && starter_dist_css_file_exists($style)
        && empty($GLOBALS['starter_dist_rendered_body_styles'][$style])
    ) {
        $GLOBALS['starter_dist_rendered_body_styles'][$style] = true;
        echo starter_dist_style_link($style);
    }

    $GLOBALS['starter_rendered_strate_count'] = (int) ($GLOBALS['starter_rendered_strate_count'] ?? 0) + 1;
    echo $html;
}

function card(mixed $name, mixed $args = []): void
{
    $name = is_scalar($name) ? trim((string) $name) : '';

    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) return;

    $name = str_starts_with($name, 'card-') ? $name : 'card-' . $name;
    if (is_numeric($args)) {
        // recuperation des données du post
        $post_id = (int) $args;
        $args = [];
    }

    get_template_part("cards/{$name}/{$name}", null, is_array($args) ? $args : []);
}

function form(
    mixed $type = 'text',
    mixed $label = null,
    mixed $name = null,
    mixed $required = false,
    mixed $mandatory = null,
    mixed $mandatory_msg = "Ce champ est obligatoire.",
    mixed $placeholder = null,
    mixed $options = null,
    mixed $args = null,
    mixed $classes = null,
    mixed $attributes = null
): void {
    if (is_array($type)) {
        $field_args = $type;

        if ($label !== null) {
            $field_args['classes'] = $label;
        }

        if ($name !== null) {
            $field_args['attributes'] = $name;
        }
    } else {
        $field_args = is_array($args) ? $args : [];
        $field_args['type'] = $type;
        $field_args['label'] = $label;
        $field_args['name'] = $name;
        $field_args['required'] = (bool) $required;
        $field_args['mandatory'] = $mandatory;
        $field_args['placeholder'] = $placeholder;
        $field_args['options'] = is_array($options) ? $options : [];

        if ($classes !== null) {
            $field_args['classes'] = $classes;
        }

        if ($attributes !== null) {
            $field_args['attributes'] = $attributes;
        }
    }

    if (!isset($field_args['mandatory_msg'])) {
        $field_args['mandatory_msg'] = $mandatory_msg;
    }

    if (empty($field_args['name']) || trim((string) $field_args['name']) === '') return;

    get_template_part('form/form', null, $field_args);
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

function html_attributes(mixed $attributes): string
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
