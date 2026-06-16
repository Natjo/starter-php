<?php
$args = normalize_args($args ?? null);
$args = [
    "pages" => [

        [
            "title" => "Golden rules",
            "url" => "#our_foundations",
        ],
        [
            "title" => "Solutions",
            "url" => "#toolkit",
        ],
        [
            "title" => "Dust",
            "url" => "#platform",
        ],
        [
            "title" => "Training",
            "url" => "#learn",
        ],
        [
            "title" => "News",
            "url" => "#ai_news",
        ],
        [
            "title" => "People",
            "url" => "#key_people",
        ],
        [
            "title" => "Hybrid AI",
            "url" => "#hybrid_ai",
        ]
    ],
];

$pages = !empty($args["pages"]) && is_array($args["pages"]) ? $args["pages"] : [];
$brand = !empty($args["brand"]) ? (string) $args["brand"] : "Site";
$current = trim(parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH), "/");
?>

<header id="header-nav" class="header-nav" role="banner">
    <a class="header-nav-logo" href="/"> <?php component::icon("logo", 122, 25); ?></a>

    <nav id="nav" aria-label="Navigation principale">
        <?php foreach ($pages as $page) :
            $title = !empty($page["title"]) ? (string) $page["title"] : "";
            $url = !empty($page["url"]) ? (string) $page["url"] : "#";
            $target = !empty($page["target"]) ? ' target="' . htmlspecialchars((string) $page["target"], ENT_QUOTES, "UTF-8") . '"' : "";
            $pagePath = trim(parse_url($url, PHP_URL_PATH) ?? "", "/");
            $isActive = $pagePath === $current;
            if ($title === "") continue;
        ?>
            <a class="header-nav-link<?= $isActive ? " is-active" : "" ?>" href="<?= htmlspecialchars($url, ENT_QUOTES, "UTF-8") ?>" <?= $target ?><?= $isActive ? ' aria-current="page"' : "" ?>>
                <?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php component::btn("Access Dust", "btn-1 cta", ["blank", 16, 16]); ?>

    <button class="btn-nav" type="button" aria-expanded="false" aria-controls="nav">
        Menu
    </button>

    <?php component::select_lang([
        ["code" => "fr", "label" => "FR", "url" => "/fr/", "current" => true],
        ["code" => "en", "label" => "EN", "url" => "/en/"],
    ]); ?>
</header>