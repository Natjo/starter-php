<?php
$args = normalize_args($args ?? null);
$list = isset($args["list"]) ? $args["list"] : [];
$classes = component::classes("shares", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$title = !empty($args["title"]) ? (string) $args["title"] : __("Partager l’article", 'lsd_lang');

static $sharesInstance = 0;
$sharesInstance++;
$titleId = "shares-title-" . $sharesInstance;

$normalizeUrl = static function (mixed $url): string {
    if (!is_string($url) || trim($url) === "") {
        return "";
    }

    $url = trim($url);

    return filter_var($url, FILTER_VALIDATE_URL) ? $url : "";
};

$currentUrl = static function () use ($normalizeUrl): string {
    $host = (string) ($_SERVER["HTTP_HOST"] ?? $_SERVER["SERVER_NAME"] ?? "");
    $uri = (string) ($_SERVER["REQUEST_URI"] ?? "");

    if ($host === "" || $uri === "") {
        return "";
    }

    $hostParts = explode(":", $host, 2);
    $hostname = strtolower($hostParts[0] ?? "");
    $port = isset($hostParts[1]) && ctype_digit($hostParts[1]) ? ":" . $hostParts[1] : "";

    if (!filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return "";
    }

    $path = parse_url($uri, PHP_URL_PATH);
    $query = parse_url($uri, PHP_URL_QUERY);
    $path = is_string($path) && $path !== "" ? $path : "/";
    $query = is_string($query) && $query !== "" ? "?" . $query : "";
    $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";

    return $normalizeUrl($scheme . "://" . $hostname . $port . $path . $query);
};

$url = $normalizeUrl($args["url"] ?? "") ?: $currentUrl();
if ($url === "") {
    return;
}

$encodedUrl = rawurlencode($url);
$encodedMailBody = rawurlencode($url);

$catalog = [
    "email" => [
        "name" => "email",
        "icon" => "mail",
        "url" => "mailto:?body=" . $encodedMailBody,
        "label" => "Partager l’article par E-mail",
    ],
    "copy" => [
        "name" => "copy",
        "icon" => "copy",
        "url" => $url,
        "label" => "Copier le lien",
    ],
    "facebook" => [
        "name" => "facebook",
        "icon" => "facebook",
        "url" => "https://www.facebook.com/sharer/sharer.php?u=" . $encodedUrl,
        "label" => "Partager l’article sur Facebook",
    ],
    "x" => [
        "name" => "x",
        "icon" => "x",
        "url" => "https://www.twitter.com/share?url=" . $encodedUrl,
        "label" => "Partager l’article sur X",
    ],
    "whatsapp" => [
        "name" => "whatsapp",
        "icon" => "whatsapp",
        "url" => "https://wa.me/?text=" . $encodedUrl,
        "label" => "Partager l’article sur Whatsapp",
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



<nav class="<?= $classes ?>" aria-labelledby="<?= esc_attr($titleId) ?>" data-context="@visible true" data-module="components/shares"<?= $attributes ?>>
    <div class="title" id="<?= esc_attr($titleId) ?>"><?= esc_html($title) ?></div>

    <ul class="list">
        <?php foreach ($keys as $key) :
            $item = $catalog[$key] ?? null;
            if (!is_array($item)) continue;
            $name = (string) ($item["name"] ?? "");
            $icon = (string) ($item["icon"] ?? "");
            $share_url = (string) ($item["url"] ?? "");
            if ($name === "" || $icon === "" || $share_url === "") continue;
            $label = (string) ($item["label"] ?? $name);
            $is_copy = $name === "copy";
        ?>
            <li>
                <button
                    type="button"
                    data-type="<?= esc_attr($name) ?>"
                    data-url="<?= esc_attr($share_url) ?>"
                >
                    <?php component::icon($icon, 40, 40); ?>
                    <span class="sr-only"><?= esc_html($label) ?></span>
                    <?php if ($is_copy) : ?>
                        <span
                            class="sr-only"
                            role="status"
                            aria-live="polite"
                            data-success="<?= esc_attr(__("Copié", 'lsd_lang')) ?>"
                            data-error="<?= esc_attr(__("Impossible de copier le lien", 'lsd_lang')) ?>"
                        ></span>
                        <div class="tip" aria-hidden="true"><?= __("Copié", 'lsd_lang') ?></div>
                    <?php endif; ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
