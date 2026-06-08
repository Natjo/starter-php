<?php
$paginationDemoPage = isset($_GET["pagination_page"]) && is_numeric($_GET["pagination_page"])
    ? max(1, (int) $_GET["pagination_page"])
    : 1;
$paginationDemoPerPage = 4;
$paginationDemoTotalItems = 48;
$paginationDemoTotalPages = (int) ceil($paginationDemoTotalItems / $paginationDemoPerPage);
$paginationDemoPage = min($paginationDemoPage, $paginationDemoTotalPages);
$paginationDemoStart = (($paginationDemoPage - 1) * $paginationDemoPerPage) + 1;
$paginationDemoEnd = min($paginationDemoTotalItems, $paginationDemoStart + $paginationDemoPerPage - 1);

$components = [
    "Accordion" => "Affiche des contenus repliables, notamment pour les FAQ et les listes de questions-réponses.",
    "Autocomplete" => "Propose des suggestions pendant la saisie afin de faciliter le choix d'une valeur.",
    "Badge" => "Affiche une information courte comme un statut, un type de contenu ou un indicateur.",
    "Button" => "Déclenche une action ou affiche un appel à l'action sous forme de bouton ou de lien.",
    "Date" => "Affiche une date avec un format visuel et une valeur sémantique adaptée.",
    "Dialog" => "Ouvre une fenêtre modale pour présenter une information ou une interaction secondaire.",
    "Eyebrow" => "Affiche un surtitre court au-dessus d'un titre, d'une catégorie ou d'un libellé éditorial.",
    "Filters" => "Filtre une liste de résultats en AJAX tout en conservant un formulaire GET fonctionnel.",
    "Header" => "Regroupe le titre, le texte introductif et le lien d'en-tête d'une section.",
    "Icon" => "Affiche une icône SVG du sprite avec une taille et un libellé accessibles.",
    "Image" => "Affiche une image simple avec ses dimensions, son texte alternatif et sa variante de taille.",
    "Link" => "Affiche un lien textuel, éventuellement accompagné d'une icône.",
    "List" => "Affiche une collection d'éléments ou de cards dans une structure de liste.",
    "Marquee" => "Fait défiler une suite de contenus et inverse son sens selon la direction du scroll.",
    "Navanchor" => "Génère une navigation interne vers les différentes sections d'une page.",
    "Newsletter" => "Affiche un formulaire d'inscription validé et envoyé en AJAX sans rechargement.",
    "Notification" => "Affiche un message temporaire de confirmation, d'information, d'avertissement ou d'erreur.",
    "Pagination" => "Navigue entre plusieurs pages de résultats avec un fallback PHP et une amélioration AJAX.",
    "Picto" => "Affiche une icône mise en forme comme un pictogramme éditorial.",
    "Picture" => "Gère une image responsive avec sources desktop et mobile, WebP, lazy loading et preload.",
    "Quote" => "Présente une citation avec son auteur, sa fonction et sa source.",
    "Search" => "Affiche un champ de recherche avec son libellé et son bouton de soumission.",
    "Select custom" => "Remplace l'affichage natif d'un select tout en conservant son comportement de formulaire.",
    "Select custom full" => "Affiche un select personnalisé enrichi occupant toute la largeur disponible.",
    "Select lang" => "Permet de passer d'une version linguistique du site à une autre.",
    "Shares" => "Propose des actions de partage vers les réseaux, par e-mail ou par copie du lien.",
    "Slider" => "Affiche une collection dans un carrousel avec navigation et pagination.",
    "Tab" => "Organise plusieurs contenus dans une interface à onglets accessible.",
    "Table" => "Affiche des données structurées dans un tableau responsive et accessible.",
    "Tag" => "Affiche une étiquette, une catégorie ou un filtre sous forme de texte, lien ou bouton.",
    "Text" => "Affiche un texte simple, introductif ou enrichi provenant des données éditoriales.",
    "Title" => "Affiche un titre avec un niveau hiérarchique, des classes et du HTML contrôlés.",
    "Tooltip" => "Affiche une aide contextuelle courte au survol et à la prise de focus.",
    "Video" => "Intègre une vidéo avec son image d'aperçu, son titre et ses contrôles de lecture.",
];
?>

<?php common('header-nav'); ?>

<main id="main">
    <?php hero("page", ["title" => "Components"]); ?>

    <div class="strate starter-styleguide">
        <section id="components-list" class="starter-section">
            <header class="starter-section-header">
                <h2 class="title title-2">Inventaire des composants</h2>
                <p><?= count($components) ?> composants réutilisables disponibles dans le starter.</p>
            </header>

            <div class="starter-grid">
                <?php foreach ($components as $name => $description) : ?>
                    <article class="starter-panel">
                        <h3 class="title title-3"><?= esc_html($name) ?></h3>
                        <p><?= esc_html($description) ?></p>

                        <div class="starter-stack">
                            <?php
                            switch ($name) {
                                case "Accordion":
                                    component::accordion([
                                        ["title" => "Question exemple", "text" => "Réponse courte du composant accordéon."],
                                        ["title" => "Deuxième question", "text" => "Autre contenu repliable."],
                                    ]);
                                    break;

                                case "Autocomplete":
                                    component::autocomplete([
                                        ["name" => "France", "value" => "fr"],
                                        ["name" => "Belgique", "value" => "be"],
                                    ], "Pays");
                                    break;

                                case "Badge":
                                    component::badge("Nouveau");
                                    break;

                                case "Button":
                                    component::btn("Découvrir");
                                    break;

                                case "Date":
                                    component::date("2026-06-06", "d F Y");
                                    break;

                                case "Dialog":
                                    component::dialog(
                                        "<p>Contenu de la fenêtre de démonstration.</p>",
                                        ["btn", "Ouvrir la fenêtre", null],
                                        "Ouvrir la fenêtre",
                                        "Fermer"
                                    );
                                    break;

                                case "Eyebrow":
                                    component::eyebrow(["eyebrow" => "Exemple de surtitre"]);
                                    break;

                                case "Filters":
                                    component::filters([
                                        "action" => "/search/",
                                        "endpoint" => "/search/search-ajax.php",
                                        "target" => "filters-demo-results",
                                        "pagination_target" => "filters-demo-pagination",
                                        "page_param" => "search_page",
                                        "query" => [
                                            "label" => "Rechercher",
                                            "name" => "q",
                                            "placeholder" => "Nom ou description",
                                        ],
                                        "filters_label" => "Types",
                                        "filter_name" => "type[]",
                                        "filters" => [
                                            ["label" => "Cards", "value" => "cards", "checked" => true],
                                            ["label" => "Components", "value" => "components", "checked" => true],
                                        ],
                                        "classes" => "starter-filters",
                                    ]);
                                    ?>
                                    <div id="filters-demo-results" tabindex="-1"></div>
                                    <div id="filters-demo-pagination"></div>
                                    <?php
                                    break;

                                case "Header":
                                    component::header([
                                        "title" => "Titre de section",
                                        "text" => "Introduction courte du composant header.",
                                    ]);
                                    break;

                                case "Icon":
                                    component::icon("email", 32, 32, null, "E-mail");
                                    break;

                                case "Image":
                                    component::image(THEME_ASSETS . "img/63-1400x1024.jpg", "full", "", true);
                                    break;

                                case "Link":
                                    component::link([
                                        "title" => "En savoir plus",
                                        "url" => "#components-list",
                                    ]);
                                    break;

                                case "List":
                                    component::list([
                                        ["title" => "Premier élément", "text" => "Description courte."],
                                        ["title" => "Deuxième élément", "text" => "Description courte."],
                                    ], "news");
                                    break;

                                case "Marquee":
                                    component::marquee([
                                        "items" => ["Design", "Développement", "Accessibilité", "Performance"],
                                        "direction" => "left",
                                        "speed" => 45,
                                    ]);
                                    break;

                                case "Navanchor":
                                    component::navanchor([
                                        ["anchor" => "components-list", "name" => "Inventaire"],
                                        ["anchor" => "footer", "name" => "Pied de page"],
                                    ], null, null, "Navigation de démonstration");
                                    break;

                                case "Newsletter":
                                    component::newsletter([
                                        "title" => "Recevez nos actualités",
                                        "text" => "<p>Une sélection courte, envoyée sans bruit inutile.</p>",
                                        "consent_label" => "J'accepte de recevoir la newsletter.",
                                    ]);
                                    break;

                                case "Notification":
                                    component::notification([
                                        "title" => "Notification",
                                        "message" => "Le composant est prêt à être utilisé.",
                                        "type" => "success",
                                        "duration" => 0,
                                    ]);
                                    break;

                                case "Pagination":
                                    ?>
                                    <div id="pagination-demo-results">
                                        <div class="pagination-demo-grid">
                                            <?php for ($item = $paginationDemoStart; $item <= $paginationDemoEnd; $item++) : ?>
                                                <article class="pagination-demo-item">
                                                    <span>Article <?= $item ?></span>
                                                    <strong>Contenu fictif de la page <?= $paginationDemoPage ?></strong>
                                                </article>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php
                                    component::pagination([
                                        "current" => $paginationDemoPage,
                                        "total" => $paginationDemoTotalPages,
                                        "range" => 2,
                                        "url" => "/components/?pagination_page={page}",
                                        "ajax" => true,
                                        "endpoint" => THEME_ASSETS . "components/pagination/pagination-ajax.php",
                                        "target" => "pagination-demo-results",
                                        "page_param" => "pagination_page",
                                    ]);
                                    break;

                                case "Picto":
                                    component::picto("email", "", "", null, null);
                                    break;

                                case "Picture":
                                    component::picture(THEME_ASSETS . "img/63-1400x1024.jpg");
                                    break;

                                case "Quote":
                                    component::quote([
                                        "text" => "Une citation courte pour illustrer le composant.",
                                        "author" => "Jane Doe",
                                        "role" => "Directrice",
                                    ]);
                                    break;

                                case "Search":
                                    component::search("Rechercher", "Votre recherche", "Rechercher");
                                    break;

                                case "Select custom":
                                    component::select_custom([
                                        ["name" => "Option 1", "value" => "option-1"],
                                        ["name" => "Option 2", "value" => "option-2"],
                                    ], "Choisir une option", "select-demo");
                                    break;

                                case "Select custom full":
                                    component::select_custom_full([
                                        ["name" => "Option A", "value" => "option-a"],
                                        ["name" => "Option B", "value" => "option-b"],
                                    ], "Choisir une option");
                                    break;

                                case "Select lang":
                                    component::select_lang([
                                        ["code" => "fr", "label" => "FR", "url" => "/components/", "current" => true],
                                        ["code" => "en", "label" => "EN", "url" => "/en/components/"],
                                    ]);
                                    break;

                                case "Shares":
                                    component::shares([
                                        "list" => ["email", "copy"],
                                        "url" => "https://example.com/article",
                                        "title" => "Partager la démonstration",
                                    ]);
                                    break;

                                case "Slider":
                                    component::slider([
                                        ["title" => "Slide 1", "text" => "Premier contenu."],
                                        ["title" => "Slide 2", "text" => "Deuxième contenu."],
                                    ], "card-news");
                                    break;

                                case "Tab":
                                    component::tab([
                                        ["label" => "Onglet 1", "content" => "<p>Premier panneau.</p>"],
                                        ["label" => "Onglet 2", "content" => "<p>Deuxième panneau.</p>"],
                                    ]);
                                    break;

                                case "Table":
                                    component::table([
                                        ["Composant", "Usage"],
                                        ["Title", "Titre hiérarchisé"],
                                        ["Text", "Contenu éditorial"],
                                    ]);
                                    break;

                                case "Tag":
                                    component::tag("Actualité");
                                    break;

                                case "Text":
                                    component::text("Texte éditorial de démonstration.");
                                    break;

                                case "Title":
                                    component::title("Titre de démonstration", 4, "title-4");
                                    break;

                                case "Tooltip":
                                    component::tooltip("Information", "Aide contextuelle du composant.");
                                    break;

                                case "Video":
                                    component::video(
                                        "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
                                        "Vidéo de démonstration"
                                    );
                                    break;
                            }
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php common('footer'); ?>
