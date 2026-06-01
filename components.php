<?php

class component
{
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
        $attributes = starter_attributes($attributes);

        return $attributes !== '' ? ' ' . $attributes : '';
    }

    public static function title(mixed $args, mixed $hx = 2, mixed $classes = null, mixed $attributes = null): void
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

    public static function text(mixed $args, mixed $classes = null, mixed $attributes = null): void
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

    public static function picture(mixed $args, mixed $sizes = "full", mixed $classes = "", bool $lazy = true, bool $placeholder = false, int $breakpoint = 768): void
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

    public static function card(mixed $name, mixed $args = []): void
    {
        $name = self::card_name($name);
        if ($name === "") return;

        get_template_part("cards/{$name}/{$name}", null, is_array($args) ? $args : []);
    }

    private static function card_name(mixed $name): string
    {
        $name = normalize_template_slug($name);

        if ($name === "" || str_contains($name, "/")) {
            return "";
        }

        $template = "cards/{$name}/{$name}.php";
        $directories = array_filter([
            APP_ROOT,
            APP_ROOT . "/assets",
            WEB_ROOT,
        ], static fn($directory) => is_dir($directory));

        foreach ($directories as $directory) {
            if (is_safe_template_file($directory . "/" . $template, $directory)) {
                return $name;
            }
        }

        return "";
    }

    public static function slider(mixed $items, mixed $card = "card-news", mixed $classes = null, bool $navigation = true, bool $pagination = true, mixed $label = ""): void
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

    public static function btn(mixed $args, mixed $classes = null, mixed $icon = [], mixed $attributes = null): void
    {
        if (is_string($args)) {
            $args = ["name" => $args];
        }

        if (!is_array($args)) return;

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if (!empty($icon)) {
            $args["icon"] = $icon;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/btn/btn', null, $args);
    }

    public static function icon(mixed $name, mixed $width, mixed $height, mixed $classes = null, mixed $label = null): void
    {
        if (empty($name)) return;

        get_template_part('components/icon', '', [
            "name" => $name,
            "width" =>  $width,
            "height" =>  $height,
            "url" =>  THEME_ASSETS,
            "classes" => $classes,
            "label" => $label,
        ]);
    }

    public static function link(mixed $args, mixed $classes = null, mixed $icon = null, mixed $attributes = null): void
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
    public static function quote(mixed $args, mixed $classes = null, mixed $attributes = null): void
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

    public static function accordion(mixed $items, mixed $multiple = false, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($items) || !is_array($items)) return;

        if (!is_bool($multiple)) {
            $attributes = $classes;
            $classes = $multiple;
            $multiple = false;
        }

        $args = isset($items["items"]) && is_array($items["items"]) ? $items : ["items" => $items];
        $accordion_items = $args["items"];

        $isList = array_keys($accordion_items) === range(0, count($accordion_items) - 1);
        if (!$isList) {
            $args["items"] = [$accordion_items];
        }

        if ($multiple !== null) {
            $args["multiple"] = (bool) $multiple;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/accordion/accordion', null, $args);
    }
    public static function header(mixed $args, mixed $classes = null, mixed $attributes = null): void
    {
        if (is_string($args)) {
            $args = ["title" => $args];
        }

        if (!is_array($args)) return;

        $has_content = !empty($args["title"])
            || !empty($args["titre"])
            || !empty($args["heading"])
            || !empty($args["headline"])
            || !empty($args["text"])
            || !empty($args["intro"])
            || !empty($args["content"])
            || !empty($args["description"])
            || !empty($args["link"])
            || !empty($args["cta"])
            || !empty($args["button"]);

        if (!$has_content) return;

        $data = $args;
        if ($classes !== null) {
            $data["classes"] = $classes;
        }
        if ($attributes !== null) {
            $data["attributes"] = $attributes;
        }

        get_template_part('components/header/header', null, $data);
    }
    public static function badge(mixed $name, mixed $classes = null, mixed $attributes = null): void
    {
        if (is_string($name)) {
            $name = ["name" => $name];
        }

        if (!is_array($name)) return;

        if ($classes !== null) {
            $name["classes"] = $classes;
        }

        if ($attributes !== null) {
            $name["attributes"] = $attributes;
        }

        get_template_part('components/badge/badge', null, $name);
    }

    /**
     * @param array<int, mixed> $trigger [0] => "btn"|"link", [1] => label (null = défaut i18n), [2] => classes CSS du déclencheur
     */
    public static function dialog(mixed $content, mixed $trigger = ["btn", null, null], mixed $classes = null, mixed $attributes = null): void
    {
        $args = [];

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
            "title" => $args["title"] ?? null,
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
    public static function form(mixed $type = 'text', mixed $label = null, mixed $name = null, bool $required = false, mixed $options = null, mixed $mandatory = null, mixed $args = null, mixed $classes = null, mixed $attributes = null): void
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

    public static function navanchor(mixed $items, mixed $classes = null, mixed $attributes = null, mixed $label = null): void
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

    public static function picto(mixed $name, mixed $type = "", mixed $size = "", mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($name)) return;

        if (is_array($name)) {
            $args = $name;
            $name = $args["name"] ?? $args["icon"] ?? "";
            $type = $args["type"] ?? $type;
            $size = $args["size"] ?? $size;
            $classes = $classes ?? ($args["classes"] ?? null);
            $attributes = $attributes ?? ($args["attributes"] ?? null);
            $label = $args["label"] ?? null;
        } else {
            $label = null;
        }

        if (empty($name)) return;

        get_template_part('components/picto/picto', null, [
            "name" => $name,
            "type" => $type,
            "size" => $size,
            "classes" => $classes,
            "attributes" => $attributes,
            "label" => $label,
        ]);
    }
    /**
     * @param array<int, array{name:string,value?:string,selected?:bool,disabled?:bool}> $args
     */
    public static function select(mixed $args, mixed $label = null, mixed $name = null, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($args) || !is_array($args)) return;

        $data = isset($args['options']) && is_array($args['options']) ? $args : ['options' => $args];

        get_template_part('components/select/select', null, [
            "args" => $data['options'],
            "label" => $label ?? ($data['label'] ?? null),
            "name" => $name ?? ($data['name'] ?? null),
            "classes" => $classes ?? ($data['classes'] ?? null),
            "attributes" => $attributes ?? ($data['attributes'] ?? null),
            "placeholder" => $data['placeholder'] ?? null,
            "required" => $data['required'] ?? false,
            "disabled" => $data['disabled'] ?? false,
            "multiple" => $data['multiple'] ?? false,
            "autocomplete" => $data['autocomplete'] ?? null,
            "aria_label" => $data['aria_label'] ?? null,
        ]);
    }

    /**
     * @param array<int, array{name:string,value?:string,selected?:bool,disabled?:bool}> $args
     */
    public static function select_custom(mixed $args, mixed $label, bool $multi = false, mixed $classes = null, mixed $attributes = null): void
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

    public static function shares(mixed $list, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($list)) return;

        $args = is_array($list) && array_key_exists("list", $list) ? $list : ["list" => $list];

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/shares/shares', null, $args);
    }

    public static function video(mixed $url, mixed $title = "", mixed $poster = null, bool $autoplay = false, bool $loop = false, mixed $classes = null, mixed $attributes = null): void
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
    public static function table(mixed $rows, mixed $classes = null, mixed $attributes = null): void
    {
        if (!is_array($rows) || empty($rows)) {
            return;
        }

        $args = array_key_exists('rows', $rows) ? $rows : ['rows' => $rows];

        if ($classes !== null) {
            $args['classes'] = $classes;
        }

        if ($attributes !== null) {
            $args['attributes'] = $attributes;
        }

        get_template_part('components/table/table', '', $args);
    }

    public static function tab(mixed $items, mixed $title = null, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($items)) return;

        $args = is_array($items) && array_key_exists("items", $items) ? $items : ["items" => $items];

        if ($title !== null) {
            $args["title"] = $title;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/tab/tab', '', $args);
    }

    public static function list(mixed $items, mixed $card = "news", mixed $classes = null): void
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

    public static function image(mixed $image, mixed $size = "full", mixed $classes = "", bool $lazy = true, mixed $attributes = null): void
    {
        if (empty($image)) return;

        if (is_array($image)) {
            $args = $image;
            $image = $args["image"] ?? $args["src"] ?? $args["url"] ?? "";
            $size = $args["size"] ?? $size;
            $classes = $classes !== "" ? $classes : ($args["classes"] ?? "");
            $lazy = array_key_exists("lazy", $args) ? (bool) $args["lazy"] : $lazy;
            $attributes = $attributes ?? ($args["attributes"] ?? null);
        } else {
            $args = [];
        }

        if (empty($image)) return;

        get_template_part('components/image/image', '', [
            "image" => $image,
            "size" => $size,
            "alt" => $args["alt"] ?? "",
            "classes" => $classes,
            "lazy" => $lazy,
            "decoding" => $args["decoding"] ?? "async",
            "fetchpriority" => $args["fetchpriority"] ?? null,
            "attributes" => $attributes,
        ]);
    }


    public static function tag(mixed $args, mixed $type = "info", mixed $classes = null, mixed $attributes = null): void
    {
        if (is_string($args)) {
            $args = ["name" => $args];
        }

        if (!is_array($args)) return;

        $allowed_types = ["info", "btn", "link"];
        if (!is_string($type) || !in_array($type, $allowed_types, true)) {
            if (is_string($type) && trim($type) !== "") {
                $attributes = $classes;
                $classes = $type;
            }
            $type = "info";
        }

        if (!isset($args["type"]) || $type !== "info") {
            $args["type"] = $type;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/tag/tag', '', $args);
    }


    public static function tooltip(mixed $label, mixed $content = null, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($label)) return;

        $args = is_array($label) ? $label : [
            "label" => $label,
            "content" => $content,
            "classes" => $classes,
            "attributes" => $attributes,
        ];

        if (!is_array($label)) {
            get_template_part('components/tooltip/tooltip', '', $args);
            return;
        }

        if ($content !== null) {
            $args["content"] = $content;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/tooltip/tooltip', '', $args);
    }
    public static function search(mixed $label = null, mixed $placeholder = null, mixed $button_label = null, mixed $action = null, mixed $classes = null, mixed $attributes = null): void
    {
        $args = is_array($label) ? $label : [
            "label" => $label,
            "placeholder" => $placeholder,
            "button_label" => $button_label,
            "action" => $action,
            "classes" => $classes,
            "attributes" => $attributes,
        ];

        if (!is_array($label)) {
            get_template_part('components/search/search', '', $args);
            return;
        }

        if ($placeholder !== null) {
            $args["placeholder"] = $placeholder;
        }

        if ($button_label !== null) {
            $args["button_label"] = $button_label;
        }

        if ($action !== null) {
            $args["action"] = $action;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/search/search', '', $args);
    }

    public static function autocomplete(mixed $items, mixed $label, mixed $classes = null, mixed $attributes = null): void
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
