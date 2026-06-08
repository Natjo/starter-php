<?php
require_once __DIR__ . "/search-data.php";

$page_title = "Recherche";
$page_description = "Rechercher parmi les cards, composants et strates du starter.";
$searchState = search_state($_GET);
$searchResult = search_results($searchState);
$searchEndpoint = "/search/search-ajax.php";
?>

<?php common("header-nav"); ?>

<main id="main">
    <?php hero("page", [
        "title" => "Recherche",
        "text" => "Explorez les cards, composants et strates disponibles dans le starter.",
    ]); ?>

    <div class="strate starter-styleguide">
        <section class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Filtrer les ressources</h2>
                <p>La recherche et les filtres fonctionnent avec ou sans JavaScript.</p>
            </header>

            <?php component::filters([
                "action" => "/search/",
                "endpoint" => $searchEndpoint,
                "target" => "search-results",
                "pagination_target" => "search-pagination",
                "page_param" => "search_page",
                "query" => [
                    "label" => "Rechercher",
                    "name" => "q",
                    "placeholder" => "Nom ou description",
                    "value" => $searchState["q"],
                ],
                "filters_label" => "Types de ressources",
                "filter_name" => "type[]",
                "filters" => [
                    ["label" => "Cards", "value" => "cards", "checked" => in_array("cards", $searchState["types"], true)],
                    ["label" => "Components", "value" => "components", "checked" => in_array("components", $searchState["types"], true)],
                    ["label" => "Strates", "value" => "strates", "checked" => in_array("strates", $searchState["types"], true)],
                ],
                "submit_label" => "Rechercher",
                "classes" => "starter-filters",
            ]); ?>
        </section>

        <section class="starter-section" aria-labelledby="search-results-title">
            <header class="starter-section-header">
                <h2 class="title title-2" id="search-results-title">Résultats</h2>
            </header>

            <div id="search-results" tabindex="-1" data-search-results>
                <?= search_render_results($searchResult) ?>
            </div>

            <div id="search-pagination">
                <?= search_render_pagination($searchState, $searchResult, $searchEndpoint) ?>
            </div>
        </section>
    </div>
</main>

<?php common("footer"); ?>
