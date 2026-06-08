<?php

function search_catalog(): array
{
    return [
        ["type" => "cards", "title" => "Card news", "description" => "Carte éditoriale avec titre, texte, lien et image facultative."],
        ["type" => "cards", "title" => "Card UI", "description" => "Carte d'interface compacte avec un titre et une description."],

        ["type" => "components", "title" => "Accordion", "description" => "Affiche des contenus repliables pour les FAQ et informations secondaires."],
        ["type" => "components", "title" => "Autocomplete", "description" => "Propose des suggestions pendant la saisie d'une valeur."],
        ["type" => "components", "title" => "Badge", "description" => "Affiche un statut ou un indicateur court."],
        ["type" => "components", "title" => "Button", "description" => "Déclenche une action ou affiche un appel à l'action."],
        ["type" => "components", "title" => "Date", "description" => "Affiche une date avec une valeur sémantique adaptée."],
        ["type" => "components", "title" => "Dialog", "description" => "Ouvre une fenêtre modale accessible."],
        ["type" => "components", "title" => "Eyebrow", "description" => "Affiche un surtitre, une catégorie ou un libellé éditorial."],
        ["type" => "components", "title" => "Header", "description" => "Regroupe le titre et l'introduction d'une section."],
        ["type" => "components", "title" => "Icon", "description" => "Affiche une icône SVG avec un libellé accessible."],
        ["type" => "components", "title" => "Image", "description" => "Affiche une image simple avec ses dimensions et son texte alternatif."],
        ["type" => "components", "title" => "Link", "description" => "Affiche un lien textuel éventuellement accompagné d'une icône."],
        ["type" => "components", "title" => "List", "description" => "Affiche une collection structurée de cards."],
        ["type" => "components", "title" => "Marquee", "description" => "Fait défiler une suite de contenus horizontalement."],
        ["type" => "components", "title" => "Navanchor", "description" => "Génère une navigation interne entre les sections d'une page."],
        ["type" => "components", "title" => "Newsletter", "description" => "Affiche un formulaire d'inscription envoyé en AJAX."],
        ["type" => "components", "title" => "Notification", "description" => "Affiche un message temporaire de statut ou d'erreur."],
        ["type" => "components", "title" => "Pagination", "description" => "Navigue entre plusieurs pages avec un fallback PHP et AJAX."],
        ["type" => "components", "title" => "Picto", "description" => "Affiche une icône sous forme de pictogramme éditorial."],
        ["type" => "components", "title" => "Picture", "description" => "Gère une image responsive, WebP, lazy loading et preload."],
        ["type" => "components", "title" => "Quote", "description" => "Présente une citation, son auteur et sa source."],
        ["type" => "components", "title" => "Search", "description" => "Affiche un champ de recherche avec son bouton."],
        ["type" => "components", "title" => "Select custom", "description" => "Personnalise l'affichage d'un champ select natif."],
        ["type" => "components", "title" => "Select custom full", "description" => "Affiche un select enrichi occupant toute la largeur."],
        ["type" => "components", "title" => "Select lang", "description" => "Permet de changer de version linguistique."],
        ["type" => "components", "title" => "Shares", "description" => "Propose des actions de partage et de copie du lien."],
        ["type" => "components", "title" => "Slider", "description" => "Affiche une collection dans un carrousel accessible."],
        ["type" => "components", "title" => "Tab", "description" => "Organise plusieurs contenus dans une interface à onglets."],
        ["type" => "components", "title" => "Table", "description" => "Affiche des données structurées dans un tableau responsive."],
        ["type" => "components", "title" => "Tag", "description" => "Affiche une étiquette, une catégorie ou un filtre."],
        ["type" => "components", "title" => "Text", "description" => "Affiche un contenu textuel simple ou enrichi."],
        ["type" => "components", "title" => "Title", "description" => "Affiche un titre avec un niveau hiérarchique contrôlé."],
        ["type" => "components", "title" => "Tooltip", "description" => "Affiche une aide contextuelle au survol et au focus."],
        ["type" => "components", "title" => "Video", "description" => "Intègre une vidéo avec aperçu et contrôles de lecture."],

        ["type" => "strates", "title" => "Strate accordion", "description" => "Section éditoriale contenant un accordéon."],
        ["type" => "strates", "title" => "Strate quote", "description" => "Section éditoriale dédiée à une citation."],
        ["type" => "strates", "title" => "Strate slider", "description" => "Section éditoriale contenant un carrousel de cards."],
        ["type" => "strates", "title" => "Strate text", "description" => "Section de texte avec titre et lien facultatif."],
        ["type" => "strates", "title" => "Strate text image", "description" => "Section associant un contenu éditorial et une image."],
    ];
}

function search_state(array $source): array
{
    $allowedTypes = ["cards", "components", "strates"];
    $query = isset($source["q"]) && is_scalar($source["q"]) ? trim((string) $source["q"]) : "";
    $types = isset($source["type"]) && is_array($source["type"]) ? $source["type"] : [];
    $types = array_values(array_intersect($allowedTypes, array_map("strval", $types)));

    if (empty($types)) {
        $types = $allowedTypes;
    }

    $page = isset($source["search_page"]) && is_numeric($source["search_page"])
        ? max(1, (int) $source["search_page"])
        : (isset($source["page"]) && is_numeric($source["page"]) ? max(1, (int) $source["page"]) : 1);

    return [
        "q" => $query,
        "types" => $types,
        "page" => $page,
    ];
}

function search_results(array $state, int $perPage = 12): array
{
    $query = function_exists("mb_strtolower")
        ? mb_strtolower($state["q"], "UTF-8")
        : strtolower($state["q"]);
    $items = array_values(array_filter(search_catalog(), static function (array $item) use ($state, $query): bool {
        if (!in_array($item["type"], $state["types"], true)) {
            return false;
        }

        if ($query === "") {
            return true;
        }

        $haystack = $item["title"] . " " . $item["description"];

        return function_exists("mb_stripos")
            ? mb_stripos($haystack, $query, 0, "UTF-8") !== false
            : stripos($haystack, $query) !== false;
    }));

    $total = count($items);
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($state["page"], $totalPages);
    $offset = ($page - 1) * $perPage;

    return [
        "items" => array_slice($items, $offset, $perPage),
        "total" => $total,
        "total_pages" => $totalPages,
        "page" => $page,
        "per_page" => $perPage,
    ];
}

function search_public_url(array $state): string
{
    $params = [];

    if ($state["q"] !== "") {
        $params[] = "q=" . rawurlencode($state["q"]);
    }

    foreach ($state["types"] as $type) {
        $params[] = "type%5B%5D=" . rawurlencode($type);
    }

    $params[] = "search_page={page}";

    return "/search/?" . implode("&", $params);
}

function search_render_results(array $result): string
{
    ob_start();
    ?>
    <header class="starter-results-header">
        <p><strong><?= (int) $result["total"] ?></strong> résultat<?= $result["total"] > 1 ? "s" : "" ?></p>
        <span>Page <?= (int) $result["page"] ?> sur <?= (int) $result["total_pages"] ?></span>
    </header>

    <?php if (empty($result["items"])) : ?>
        <div class="starter-empty">
            <h2>Aucun résultat</h2>
            <p>Essayez un autre terme ou élargissez les filtres.</p>
        </div>
    <?php else : ?>
        <div class="starter-results-grid">
            <?php foreach ($result["items"] as $item) : ?>
                <?php card("ui", [
                    "title" => $item["title"],
                    "description" => $item["description"],
                    "type" => ucfirst($item["type"]),
                ]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php

    return (string) ob_get_clean();
}

function search_render_pagination(array $state, array $result, string $endpoint, string $target = "search-results"): string
{
    if ($result["total_pages"] <= 1) {
        return "";
    }

    ob_start();
    component::pagination([
        "current" => $result["page"],
        "total" => $result["total_pages"],
        "range" => 2,
        "url" => search_public_url($state),
        "ajax" => true,
        "endpoint" => $endpoint,
        "target" => $target,
        "page_param" => "search_page",
        "aria_label" => "Pagination des résultats",
    ]);

    return (string) ob_get_clean();
}
