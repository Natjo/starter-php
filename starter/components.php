<?php

/**
 * Thin component dispatcher.
 *
 * Each component is autonomous: all of its logic (argument normalization +
 * markup) lives in its own template at `components/<name>/<name>.php`.
 *
 * `component::foo($a, $b, ...)` forwards the positional arguments to the
 * matching template, where they are available as the `$params` array.
 * Method names map to folders by converting underscores to hyphens
 * (e.g. `select_lang` -> `select-lang`), with explicit aliases below.
 *
 * `classes()`, `attributes()` and `form()` remain real methods because they
 * are utilities, not templates.
 */
class component
{
    /** @var array<string, string> method name => component folder */
    private const ALIASES = [
        'select' => 'select-custom',
    ];

    public static function classes(mixed ...$classes): string
    {
        $classes = array_map(static function ($class) {
            if (is_array($class)) {
                return implode(' ', array_filter($class, static fn($item) => is_scalar($item)));
            }

            return $class;
        }, $classes);

        return esc_attr(sanitize_class_list(implode(' ', array_filter($classes, static fn($class) => $class !== null && $class !== false && $class !== ''))));
    }

    public static function attributes(mixed $attributes): string
    {
        $attributes = html_attributes($attributes);

        return $attributes !== '' ? ' ' . $attributes : '';
    }

    public static function form(
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
        form($type, $label, $name, $required, $mandatory, $mandatory_msg, $placeholder, $options, $args, $classes, $attributes);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        $folder = self::ALIASES[$name] ?? str_replace('_', '-', $name);

        get_template_part("components/{$folder}/{$folder}", null, ["params" => $arguments]);
    }
}
