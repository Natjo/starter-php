<?php
$first = $params[0] ?? null;

// Deux formes d'appel :
// - component::shares($title, $list, $classes, $attributes)
// - component::shares($list, $classes, $attributes)
if (is_string($first)) {
    $shares_title = $first;
    $shares_list = $params[1] ?? null;
    $shares_classes = $params[2] ?? null;
    $shares_attributes = $params[3] ?? null;
} else {
    $shares_title = null;
    $shares_list = $first;
    $shares_classes = $params[1] ?? null;
    $shares_attributes = $params[2] ?? null;
}

if (empty($shares_list)) return;
$args = is_array($shares_list) && array_key_exists("list", $shares_list) ? $shares_list : ["list" => $shares_list];
if ($shares_title !== null) $args["title"] = $shares_title;
if ($shares_classes !== null) $args["classes"] = $shares_classes;
if ($shares_attributes !== null) $args["attributes"] = $shares_attributes;
$args = normalize_args($args);
$list = isset($args["list"]) ? $args["list"] : [];
$classes = component::classes("shares", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$title = !empty($args["title"]) ? trim((string) $args["title"]) : "";

static $sharesInstance = 0;
$sharesInstance++;
$titleId = "shares-title-" . $sharesInstance;

$normalizeUrl = static function (mixed $url): string {
    if (!is_string($url) || trim($url) === "") {
        return "";
    }

    $url = trim($url);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return "";
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    return in_array($scheme, ["http", "https", "mailto"], true) ? $url : "";
};

$normalizeEmailUrl = static function (mixed $value) use ($normalizeUrl): string {
    if (!is_string($value) || trim($value) === "") {
        return "";
    }

    $value = trim($value);
    $url = $normalizeUrl($value);
    if ($url !== "") {
        return $url;
    }

    $email = str_replace("#", "@", $value);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "";
    }

    return "mailto:" . $email;
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

$encodedUrl = rawurlencode($url);
$encodedMailBody = rawurlencode($url);

$catalog = [
    "email" => [
        "name" => "email",
        "icon" => "email",
        "url" => $url !== "" ? "mailto:?body=" . $encodedMailBody : "",
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
        "url" => $url !== "" ? "https://www.facebook.com/sharer/sharer.php?u=" . $encodedUrl : "",
        "label" => "Partager l’article sur Facebook",
    ],
    "linkedin" => [
        "name" => "linkedin",
        "icon" => "linkedin",
        "url" => $url !== "" ? "https://www.linkedin.com/sharing/share-offsite/?url=" . $encodedUrl : "",
        "label" => "Partager l’article sur LinkedIn",
    ],
    "x" => [
        "name" => "x",
        "icon" => "x",
        "url" => $url !== "" ? "https://www.twitter.com/share?url=" . $encodedUrl : "",
        "label" => "Partager l’article sur X",
    ],
    "whatsapp" => [
        "name" => "whatsapp",
        "icon" => "whatsapp",
        "url" => $url !== "" ? "https://wa.me/?text=" . $encodedUrl : "",
        "label" => "Partager l’article sur Whatsapp",
    ],
];

$items = [];
if (is_array($list)) {
    $isAssoc = $list !== [] && array_keys($list) !== range(0, count($list) - 1);

    foreach ($list as $key => $value) {
        $key = $isAssoc && is_string($key)
            ? trim($key)
            : (is_string($value) ? trim($value) : (is_array($value) && isset($value["name"]) ? trim((string) $value["name"]) : ""));
        $key = strtolower($key);
        if ($key === "" || !isset($catalog[$key])) {
            continue;
        }

        $item = $catalog[$key];
        $customUrl = "";
        $customLabel = "";
        $customIcon = "";

        if (is_array($value)) {
            $customUrl = $key === "email"
                ? $normalizeEmailUrl($value["url"] ?? $value["email"] ?? "")
                : $normalizeUrl($value["url"] ?? "");
            $customLabel = isset($value["label"]) && is_scalar($value["label"]) ? trim((string) $value["label"]) : "";
            $customIcon = isset($value["icon"]) && is_scalar($value["icon"]) ? trim((string) $value["icon"]) : "";
        } elseif ($isAssoc && is_string($value) && trim($value) !== "") {
            $customUrl = $key === "email" ? $normalizeEmailUrl($value) : $normalizeUrl($value);
        }

        if ($customUrl !== "") {
            $item["url"] = $customUrl;
        }

        if ($customLabel !== "") {
            $item["label"] = $customLabel;
        }

        if ($customIcon !== "") {
            $item["icon"] = $customIcon;
        }

        if (($item["url"] ?? "") !== "") {
            $items[] = $item;
        }
    }
}
if (empty($items)) {
    return;
}
?>



<nav class="<?= $classes ?>" <?= $title !== "" ? 'aria-labelledby="' . esc_attr($titleId) . '"' : 'aria-label="Partager"' ?> data-context="@visible true" data-module="components/shares"<?= $attributes ?>>
    <?php if ($title !== "") : ?>
        <div class="title" id="<?= esc_attr($titleId) ?>"><?= esc_html($title) ?></div>
    <?php endif; ?>

    <ul>
        <?php foreach ($items as $item) :
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
                            data-success="<?= esc_attr("Copié") ?>"
                            data-error="<?= esc_attr("Impossible de copier le lien") ?>"
                        ></span>
                        <div class="tip" aria-hidden="true"><?= "Copié" ?></div>
                    <?php endif; ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
