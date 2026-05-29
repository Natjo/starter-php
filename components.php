<?php

class component
{
    public static function args($args, array $defaults = []): array
    {
        return array_replace($defaults, is_array($args) ? $args : []);
    }

    public static function classes(...$classes)
    {
        $classes = array_map(static function ($class) {
            if (is_array($class)) {
                return implode(' ', array_filter($class, static fn($item) => is_scalar($item)));
            }

            return $class;
        }, $classes);

        return esc_attr(sanitize_class_list(implode(' ', array_filter($classes, static fn($class) => $class !== null && $class !== false && $class !== ''))));
    }

    public static function attributes($attributes)
    {
        $attributes = starter_attributes($attributes);

        return $attributes !== '' ? ' ' . $attributes : '';
    }

    public static function title($args, $hx = 2, $classes = null, $attributes = null)
    {
        if (is_string($args)) {
            $args = ["title" => $args];
        }

        if (!is_array($args)) return;

        if ($hx !== null) {
            $args["hx"] = $hx;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/title/title', null, $args);
    }

    public static function text($args, $classes = null, $attributes = null)
    {
        if (is_string($args)) {
            $args = ["text" => $args];
        }

        if (!is_array($args)) return;

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/text/text', null, $args);
    }

    public static function picture($args, $sizes = "full", $classes = "", $lazy = true, $placeholder = false, $breakpoint = 768)
    {
        // $sizes accepte :
        // - une string  → desktop seulement (mobile retombe sur "full")
        // - un tableau  → [desktop, mobile] (index 0 = desktop, index 1 = mobile)
        if (is_array($sizes)) {
            $desktopSize = isset($sizes[0]) && $sizes[0] !== "" ? (string) $sizes[0] : "full";
            $mobileSize = isset($sizes[1]) && $sizes[1] !== "" ? (string) $sizes[1] : "full";
        } else {
            $desktopSize = is_string($sizes) && $sizes !== "" ? $sizes : "full";
            $mobileSize = "full";
        }

        get_template_part('components/picture/picture', null, [
            "images" => is_array($args) ? ($args["images"] ?? $args) : $args,
            "classes" => $classes,
            "lazy" => $lazy,
            "breakpoint" => $breakpoint,
            "placeholder" => $placeholder,
            "desktop_size" => $desktopSize,
            "mobile_size" => $mobileSize,
        ]);
    }

    public static function card($name, $args = [])
    {
        if (empty($name)) return;

        get_template_part("cards/{$name}/{$name}", null, is_array($args) ? $args : []);
    }

    public static function slider($items, $card = "card-news", $classes = null, $navigation = true, $pagination = true, $label = "")
    {
        if (empty($items) || !is_array($items)) return;

        $isList = array_keys($items) === range(0, count($items) - 1);
        if (!$isList) {
            $items = [$items];
        }

        get_template_part('components/slider/slider', null, [
            "items" => $items,
            "card" => $card,
            "classes" => $classes ?? "",
            "navigation" => $navigation,
            "pagination" => $pagination,
            "label" => $label,
        ]);
    }

    public static function btn($args, $classes = null, $icon = [], $attributes = null)
    {
        if (empty($args)) return;

        if (is_string($args)) {
            $args = ["name" => $args];
        }

        if (!is_array($args)) return;

        if (!empty($args["button"]) && is_array($args["button"])) {
            $args = $args["button"];
        }

        $link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : null;
        $name = !empty($args["name"]) ? $args["name"] : "";

        if (empty($link) && empty($name)) return;
        if (!empty($link) && empty($link["title"])) return;

        get_template_part('components/btn/btn', null, [
            "name" => $name,
            "link" => $link,
            "classes" => $classes ?? ($args["classes"] ?? ""),
            "icon" => !empty($icon) ? $icon : ($args["icon"] ?? []),
            "attributes" => $attributes ?? ($args["attributes"] ?? "")
        ]);
    }

    public static function icon($name, $width, $height, $classes = null)
    {
        if (empty($name)) return;

        get_template_part('components/icon', '', [
            "name" => $name,
            "width" =>  $width,
            "height" =>  $height,
            "url" =>  THEME_ASSETS,
            "classes" => $classes
        ]);
    }

    public static function link($args, $classes = null, $icon = null, $attributes = null)
    {
        if (empty($args)) return;

        if (is_string($args)) {
            $args = [
                "link" => [
                    "url" => $args,
                    "title" => $args
                ]
            ];
        }

        if (!is_array($args)) return;

        if (!empty($args["button"]) && is_array($args["button"])) {
            $args = $args["button"];
        }

        $link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : $args;

        if (empty($link["title"])) return;

        get_template_part('components/link/link', null, [
            "link" => $link,
            "classes" => $classes ?? ($args["classes"] ?? ""),
            "icon" => $icon ?? ($args["icon"] ?? null),
            "attributes" => $attributes ?? ($args["attributes"] ?? "")
        ]);
    }
    public static function quote($args, $classes = null, $attributes = null)
    {
        if (is_string($args)) {
            $args = ["text" => $args];
        }

        if (!is_array($args)) return;

        if (!empty($args["quote"]) && is_array($args["quote"])) {
            $args = $args["quote"];
        }

        if (empty($args["text"]) && empty($args["quote"])) return;

        get_template_part('components/quote/quote', null, [
            "quote" => $args,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    public static function accordion($items, $classes = null, $attributes = null)
    {
        if (empty($items) || !is_array($items)) return;

        $isList = array_keys($items) === range(0, count($items) - 1);
        if (!$isList) {
            $items = [$items];
        }

        get_template_part('components/accordion/accordion', null, [
            "items" => $items,
            "classes" => $classes ?? "",
            "attributes" => $attributes ?? ""
        ]);
    }
    public static function header($args, $classes = null, $attributes = null)
    {
        if (!is_array($args)) return;
        if (empty($args["title"]) && empty($args["text"]) && empty($args["link"])) return;



        $data = $args;
        if ($classes) {
            $data["classes"] = $classes;
        }
        if ($attributes) {
            $data["attributes"] = $attributes;
        }

        get_template_part('components/header/header', null, $data);
    }
    public static function badge($name, $classes = null, $attributes = null)
    {
        if (empty($name)) return;

        if (is_array($name)) {
            $args = $name;
            $name = $args["name"] ?? $args["label"] ?? $args["title"] ?? "";
            $classes = $classes ?? ($args["classes"] ?? null);
            $attributes = $attributes ?? ($args["attributes"] ?? null);
        }

        if (empty($name)) return;

        $args = [
            "name" => $name,
            "classes" => $classes,
            "attributes" => $attributes,
        ];

        get_template_part('components/badge/badge', null, $args);
    }

    /**
     * @param array<int, mixed> $trigger [0] => "btn"|"link", [1] => label (null = défaut i18n), [2] => classes CSS du déclencheur
     */
    public static function dialog($content, $trigger = ["btn", null, null], $classes = null, $attributes = null)
    {
        if (is_array($content)) {
            $args = $content;
            $content = $args["content"] ?? "";
            $trigger = $args["trigger"] ?? $trigger;
            $classes = $classes ?? ($args["classes"] ?? null);
            $attributes = $attributes ?? ($args["attributes"] ?? null);
        }

        if (trim((string) $content) === "") return;

        if (!is_array($trigger)) {
            $trigger = ["btn", null, null];
        }
        $t = array_values($trigger);
        $t = array_pad($t, 3, null);
        $kind = isset($t[0]) ? strtolower(trim((string) $t[0])) : "btn";
        $kind = $kind === "link" ? "link" : "btn";
        $trigger = [
            $kind,
            ($t[1] !== null && trim((string) $t[1]) !== "") ? trim((string) $t[1]) : null,
            ($t[2] !== null && trim((string) $t[2]) !== "") ? trim((string) $t[2]) : null,
        ];

        get_template_part('components/dialog/dialog', null, [
            "content" => $content,
            "trigger" => $trigger,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    /**
     * Champ formulaire : text, textarea, select, checkbox(es), radio(s), number, email, url, tel, date, password.
     *
     * @param array|null $options Options pour select, checkboxes ou radios.
     * @param string|null $mandatory Message data-mandatory. Vide ou null = message navigateur.
     * @param array|null $args typemismatch, autocomplete, minlength, rows, pattern, min, max, data_patternmismatch, hint, checked, placeholder
     */
    public static function form($type = 'text', $label = null, $name = null, $required = false, $options = null, $mandatory = null, $args = null, $classes = null, $attributes = null)
    {
        if (is_array($type)) {
            $field_args = $type;
            $type = $field_args['type'] ?? 'text';
            $label = $field_args['label'] ?? null;
            $name = $field_args['name'] ?? null;
            $required = !empty($field_args['required']);
            $options = $field_args['options'] ?? null;
            $mandatory = $field_args['mandatory'] ?? null;
            $classes = $classes ?? ($field_args['classes'] ?? null);
            $attributes = $attributes ?? ($field_args['attributes'] ?? null);
            $args = $field_args;
        }

        if ($name === null || trim((string) $name) === '') {
            return;
        }

        $field_args = is_array($args) ? $args : [];
        unset($field_args['type'], $field_args['label'], $field_args['name'], $field_args['required'], $field_args['options'], $field_args['mandatory'], $field_args['classes'], $field_args['attributes']);
        $mandatory_msg = null;
        if (is_string($mandatory)) {
            $mandatory_msg = trim($mandatory);
            if ($mandatory_msg === '') {
                $mandatory_msg = null;
            }
        }
        $type_normalized = strtolower(trim((string) $type));
        $placeholder = $field_args['placeholder'] ?? null;
        $placeholder_types = ['text', 'email', 'date', 'tel', 'number', 'password'];
        $autocomplete_types = ['text', 'email', 'tel', 'number', 'date', 'password'];
        if (!in_array($type_normalized, $placeholder_types, true)) {
            $placeholder = null;
        }
        if (!in_array($type_normalized, $autocomplete_types, true)) {
            unset($field_args['autocomplete']);
        }
        if ($type_normalized !== 'textarea') {
            unset($field_args['rows']);
        }
        if ($type_normalized !== 'text') {
            unset($field_args['minlength']);
        }
        if ($type_normalized !== 'number') {
            unset($field_args['min'], $field_args['max']);
        }
        $pattern = isset($field_args['pattern']) ? trim((string) $field_args['pattern']) : '';
        if ($pattern === '') {
            unset($field_args['data_patternmismatch']);
        }

        get_template_part('components/form/form', null, [
            'type' => $type,
            'label' => $label,
            'name' => $name,
            'required' => (bool) $required,
            'placeholder' => $placeholder,
            'options' => is_array($options) ? $options : [],
            'typemismatch' => $field_args['typemismatch'] ?? null,
            'autocomplete' => (in_array($type_normalized, $autocomplete_types, true) && !empty($field_args['autocomplete']))
                ? $field_args['autocomplete']
                : null,
            'minlength' => ($type_normalized === 'text' && isset($field_args['minlength']))
                ? (int) $field_args['minlength']
                : null,
            'rows' => ($type_normalized === 'textarea' && isset($field_args['rows']))
                ? max(2, (int) $field_args['rows'])
                : null,
            'pattern' => $pattern !== '' ? $pattern : null,
            'min' => ($type_normalized === 'number' && isset($field_args['min']) && $field_args['min'] !== '')
                ? $field_args['min']
                : null,
            'max' => ($type_normalized === 'number' && isset($field_args['max']) && $field_args['max'] !== '')
                ? $field_args['max']
                : null,
            'data_patternmismatch' => ($pattern !== '' && !empty($field_args['data_patternmismatch']))
                ? $field_args['data_patternmismatch']
                : null,
            'hint' => $field_args['hint'] ?? null,
            'checked' => !empty($field_args['checked']),
            'mandatory' => $mandatory_msg,
            'classes' => $classes,
            'attributes' => $attributes,
        ]);
    }

    public static function navanchor($items, $classes = null, $attributes = null, $label = null)
    {
        if (empty($items)) return;

        if (isset($items["items"]) && is_array($items["items"])) {
            $args = $items;
            $items = $args["items"];
            $classes = $classes ?? ($args["classes"] ?? null);
            $attributes = $attributes ?? ($args["attributes"] ?? null);
            $label = $label ?? ($args["label"] ?? null);
        }

        $args = [
            "items" => $items,
            "classes" => $classes,
            "attributes" => $attributes,
            "label" => $label,
        ];

        get_template_part('components/navanchor/navanchor', null, $args);
    }

    public static function picto($name, $type = "", $size = "", $classes = null, $attributes = null)
    {
        if (empty($name)) return;

        if (is_array($name)) {
            $args = $name;
            $name = $args["name"] ?? $args["icon"] ?? "";
            $type = $args["type"] ?? $type;
            $size = $args["size"] ?? $size;
            $classes = $classes ?? ($args["classes"] ?? null);
            $attributes = $attributes ?? ($args["attributes"] ?? null);
        }

        if (empty($name)) return;

        get_template_part('components/picto/picto', null, [
            "name" => $name,
            "type" => $type,
            "size" => $size,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }
    /**
     * @param array<int, array{name:string,value?:string,selected?:bool,disabled?:bool}> $args
     */
    public static function select($args, $label = null, $name = null, $classes = null, $attributes = null)
    {
        if (empty($args) || !is_array($args)) return;


        get_template_part('components/select/select', null, [
            "args" => $args,
            "label" => $label,
            "name" => $name,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    /**
     * @param array<int, array{name:string,value?:string,selected?:bool,disabled?:bool}> $args
     */
    public static function select_custom($args, $label, $multi = false, $classes = null, $attributes = null)
    {
        if (empty($args) || !is_array($args)) return;


        get_template_part('components/select-custom/select-custom', null, [
            "args" => $args,
            "label" => $label,
            "multi" => $multi,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    public static function shares($list, $classes = null)
    {
        if (empty($list)) return;

        $args = [
            "list" => $list,
            "classes" => $classes
        ];

        get_template_part('components/shares/shares', null, $args);
    }

    public static function video($url, $title = "", $poster = null, $autoplay = false, $loop = false, $classes = null, $attributes = null)
    {
        if (empty($url)) return;

        get_template_part('components/video/video', '', [
            "url" => $url,
            "title" => $title,
            "poster" => $poster,
            "autoplay" => $autoplay,
            "loop" => $loop,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    /**
     * @param array $rows Tableau de lignes ; chaque ligne est un tableau de colonnes (string ou cellule).
     *                    La première ligne alimente le <thead>.
     */
    public static function table($rows, $classes = null, $attributes = null)
    {
        if (!is_array($rows) || empty($rows)) {
            return;
        }
        get_template_part('components/table/table', '', [
            'rows' => $rows,
            'classes' => $classes,
            'attributes' => $attributes,
        ]);
    }

    public static function list($items, $card = "news", $classes = null)
    {
        if (empty($items)) return;

        if ($card === "news") {
            $card = "card-news";
        }

        $args = [
            "items" => $items,
            "card" => $card,
            "classes" => $classes,
        ];
        get_template_part('components/list/list', '', $args);
    }

    public static function image($image, $size = "full", $classes = "", $lazy = true)
    {
        if (empty($image)) return;

        get_template_part('components/image/image', '',   [
            "image" => $image,
            "size" => $size,
            "classes" => $classes,
            "lazy" => $lazy,
        ]);
    }


    public static function tag($args, $type = "info", $classes = null, $attributes = null)
    {
        get_template_part('components/tag/tag', '', [
            "args" => $args,
            "type" => $type,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }


    public static function tooltip($label, $content, $classes = null, $attributes = null)
    {
        if (empty($label) || empty($content)) return;

        get_template_part('components/tooltip/tooltip', '', [
            "label" => $label,
            "content" => $content,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }
    public static function search($label = null, $placeholder = null, $button_label = null, $action = null, $classes = null, $attributes = null)
    {
        get_template_part('components/search/search', '', [
            "label" => $label,
            "placeholder" => $placeholder,
            "button_label" => $button_label,
            "action" => $action,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    public static function autocomplete($items, $label, $classes = null, $attributes = null)
    {
        if (empty($items) || !is_array($items)) return;

        get_template_part('components/autocomplete/autocomplete', '', [
            "items" => $items,
            "label" => $label,
            "classes" => $classes,
            "attributes" => $attributes,
        ]);
    }

    
}
