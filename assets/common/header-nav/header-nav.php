<?php
$args = normalize_args($args ?? null);
$args = [
    "brand" => "Mon projet",
    "pages" => [
        [
            "title" => "Accueil",
            "url" => "/",
        ],
                [
            "title" => "Styles",
            "url" => "/styles",
        ],
        [
            "title" => "Components",
            "url" => "/components",
        ],
        [
            "title" => "Strates",
            "url" => "/strates",
        ],
                [
            "title" => "Layout",
            "url" => "/layout",
        ],
        [
            "title" => "Form",
            "url" => "/form",
        ]
    ],
];

$pages = !empty($args["pages"]) && is_array($args["pages"]) ? $args["pages"] : [];
$brand = !empty($args["brand"]) ? (string) $args["brand"] : "Site";
$current = trim(parse_url($_SERVER["REQUEST_URI"] ?? "/", PHP_URL_PATH), "/");
?>

<header class="header-nav">
    <a class="header-nav-brand" href="/"><?= htmlspecialchars($brand, ENT_QUOTES, "UTF-8") ?></a>

    <?php if (!empty($pages)) : ?>
        <button class="header-nav-toggle" type="button" aria-expanded="false" aria-controls="header-nav-menu">
            Menu
        </button>

        <nav id="header-nav-menu" class="header-nav-menu" aria-label="Navigation principale">
            <?php foreach ($pages as $page) :
                $title = !empty($page["title"]) ? (string) $page["title"] : "";
                $url = !empty($page["url"]) ? (string) $page["url"] : "#";
                $target = !empty($page["target"]) ? ' target="' . htmlspecialchars((string) $page["target"], ENT_QUOTES, "UTF-8") . '"' : "";
                $pagePath = trim(parse_url($url, PHP_URL_PATH) ?? "", "/");
                $isActive = $pagePath === $current;

                if ($title === "") {
                    continue;
                }
            ?>
                <a class="header-nav-link<?= $isActive ? " is-active" : "" ?>" href="<?= htmlspecialchars($url, ENT_QUOTES, "UTF-8") ?>" <?= $target ?><?= $isActive ? ' aria-current="page"' : "" ?>>
                    <?= htmlspecialchars($title, ENT_QUOTES, "UTF-8") ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</header>