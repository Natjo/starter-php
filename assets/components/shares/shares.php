<?php
$list = isset($args["list"]) ? $args["list"] : [];
$classes = !empty($args["classes"]) ? " " . (string) $args["classes"] : "";

$host = $_SERVER["HTTP_HOST"] ?? "";
$uri = $_SERVER["REQUEST_URI"] ?? "";
$scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$url = ($host && $uri) ? "{$scheme}://{$host}{$uri}" : "";
$urlencode = urlencode($url);

$catalog = [
    "email" => [
        "name" => "email",
        "icon" => "mail",
        "url" => "mailto:?body=" . $url,
        "role" => "link",
        "label" => __("S'ouvre dans un nouvel onglet: Partager l’article par E-mail", 'lsd_lang'),
    ],
    "copy" => [
        "name" => "copy",
        "icon" => "copy",
        "url" => $url,
        "role" => "",
        "label" => __("Copier le lien", 'lsd_lang'),
    ],
    "facebook" => [
        "name" => "facebook",
        "icon" => "facebook",
        "url" => "http://facebook.com/sharer/sharer.php?u=" . $urlencode,
        "role" => "link",
        "label" => __("S'ouvre dans un nouvel onglet: Partager l’article sur Facebook", 'lsd_lang'),
    ],
    "x" => [
        "name" => "x",
        "icon" => "x",
        "url" => "https://www.twitter.com/share?url=" . $urlencode,
        "role" => "link",
        "label" => __("S'ouvre dans un nouvel onglet: Partager l’article sur X", 'lsd_lang'),
    ],
    "whatsapp" => [
        "name" => "whatsapp",
        "icon" => "whatsapp",
        "url" => "https://wa.me/?text=" . $urlencode,
        "role" => "link",
        "label" => __("S'ouvre dans un nouvel onglet: Partager l’article sur whatsapp", 'lsd_lang'),
    ],
];

$keys = [];
if (is_array($list)) {
    foreach ($list as $value) {
        $key = is_string($value) ? trim($value) : (is_array($value) && isset($value["name"]) ? trim((string) $value["name"]) : "");
        $key = strtolower($key);
        if ($key !== "" && isset($catalog[$key])) {
            $keys[] = $key;
        }
    }
}
if (empty($keys)) {
    $keys = array_keys($catalog);
}
?>



<div class="shares<?= esc_attr($classes) ?>" data-context="@visible true" data-module="common/shares">
    <div class="title"><?= __("Partager l’article", 'lsd_lang') ?></div>

    <ul class="list">
        <?php foreach ($keys as $key) :
            $item = $catalog[$key] ?? null;
            if (!is_array($item)) continue;
            $name = (string) ($item["name"] ?? "");
            $icon = (string) ($item["icon"] ?? "");
            $share_url = (string) ($item["url"] ?? "");
            if ($name === "" || $icon === "" || $share_url === "") continue;
            $role = (string) ($item["role"] ?? "");
            $label = (string) ($item["label"] ?? $name);
            $is_copy = $name === "copy";
        ?>
            <li>
                <button
                    <?= $role !== "" ? 'role="' . esc_attr($role) . '"' : "" ?>
                    data-type="<?= esc_attr($name) ?>"
                    data-url="<?= esc_attr($share_url) ?>"
                >
                    <?php component::icon($icon, 40, 40); ?>
                    <span class="sr-only"><?= esc_html($label) ?></span>
                    <?php if ($is_copy) : ?>
                        <span class="sr-only" role="status" data-text="<?= esc_attr(__("Copié", 'lsd_lang')) ?>"></span>
                        <div class="tip" aria-hidden="true"><?= __("Copié", 'lsd_lang') ?></div>
                    <?php endif; ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>