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

    public static function date(mixed $args, mixed $format = 'd/m/Y', mixed $classes = null, mixed $attributes = null): void
    {
        if ($args instanceof DateTimeInterface || is_scalar($args)) {
            $args = ["date" => $args];
        }

        if (!is_array($args)) return;

        if ($format !== null) {
            $args["format"] = $format;
        }

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/date/date', null, $args);
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

    public static function slider(
        mixed $items,
        mixed $card = "card-news",
        mixed $classes = null,
        bool $navigation = true,
        bool $pagination = true,
        mixed $aria_label = "Carrousel",
        mixed $prev_label = "Diapositive précédente",
        mixed $next_label = "Diapositive suivante",
        mixed $pagination_label = "Navigation du carrousel"
    ): void {
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
            "aria_label" => $aria_label,
            "prev_label" => $prev_label,
            "next_label" => $next_label,
            "pagination_label" => $pagination_label,
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

    public static function icon(mixed $name, mixed $width = 24, mixed $height = 24, mixed $classes = null, mixed $label = null, mixed $attributes = null): void
    {
        if (is_array($name)) {
            $args = $name;
        } else {
            if (empty($name)) return;

            $args = [
                "name" => $name,
                "width" => $width,
                "height" => $height,
                "classes" => $classes,
                "label" => $label,
                "attributes" => $attributes,
            ];
        }

        get_template_part('components/icon/icon', null, [
            "name" => $args["name"] ?? "",
            "width" =>  $args["width"] ?? 24,
            "height" =>  $args["height"] ?? 24,
            "url" =>  THEME_ASSETS,
            "classes" => $args["classes"] ?? null,
            "label" => $args["label"] ?? null,
            "attributes" => $args["attributes"] ?? null,
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
     * @param array<int, mixed> $trigger [0] => "btn"|"link", [1] => label, [2] => classes CSS du déclencheur
     */
    public static function dialog(mixed $content, mixed $trigger = ["btn", null, null], mixed $trigger_label = "Open dialog", mixed $close_label = "Close", mixed $aria_label = "Dialog", mixed $classes = null, mixed $attributes = null): void
    {
        $args = is_array($content) ? $content : ["content" => $content];

        if (!is_array($content)) {
            $args["trigger"] = $trigger;
        }

        $args["trigger_label"] = $args["trigger_label"] ?? $trigger_label;
        $args["close_label"] = $args["close_label"] ?? $close_label;
        $args["aria_label"] = $args["aria_label"] ?? $aria_label;

        if ($classes !== null) {
            $args["classes"] = $classes;
        }

        if ($attributes !== null) {
            $args["attributes"] = $attributes;
        }

        get_template_part('components/dialog/dialog', null, $args);
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

        get_template_part('components/form/form', null, $field_args);
    }

    public static function navanchor(
        mixed $items,
        mixed $classes = null,
        mixed $attributes = null,
        mixed $label = "Table des matières"
    ): void {
        if (empty($items)) return;

        get_template_part('components/navanchor/navanchor', null, [
            "items" => $items,
            "classes" => $classes,
            "attributes" => $attributes,
            "label" => $label,
        ]);
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
        self::select_custom($args, $label, $name, $classes, $attributes);
    }

    public static function select_custom(mixed $args, mixed $label = null, mixed $name = null, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($args) || !is_array($args)) return;

        $data = isset($args['options']) && is_array($args['options']) ? $args : ['options' => $args];

        get_template_part('components/select-custom/select-custom', null, [
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
    public static function select_custom_full(mixed $args, mixed $label = null, bool $multi = false, mixed $classes = null, mixed $attributes = null): void
    {
        if (empty($args) || !is_array($args)) return;

        $data = isset($args['options']) && is_array($args['options']) ? $args : ['options' => $args];

        get_template_part('components/select-custom-full/select-custom-full', null, [
            "options" => $data['options'],
            "label" => $label ?? ($data['label'] ?? null),
            "name" => $data['name'] ?? null,
            "required" => $data['required'] ?? false,
            "mandatory" => $data['mandatory'] ?? null,
            "multi" => $multi || !empty($data['multi']) || !empty($data['multiple']),
            "classes" => $classes ?? ($data['classes'] ?? null),
            "attributes" => $attributes ?? ($data['attributes'] ?? null),
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

    public static function video(
        mixed $url,
        mixed $title = "Lecteur vidéo",
        mixed $poster = null,
        bool $autoplay = false,
        bool $loop = false,
        mixed $classes = null,
        mixed $attributes = null,
        mixed $play_label = "Lire la vidéo"
    ): void {
        if (empty($url)) return;

        get_template_part('components/video/video', '', [
            "url" => $url,
            "title" => $title,
            "poster" => $poster,
            "play_label" => $play_label,
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
