<?php
get_template_part('header');

get_template_part('header-nav', null);
?>

<main>
   <?php
    hero("homepage", [
        "title" => "Components",
        "text" => ""
    ]);
    ?>

    <div class="strate">
        <?php component::badge("ret") ?>
    </div>

    <div class="strate">

        <?php component::dialog("<p>lorem</p>", ['btn', 'Open dialog', null]); ?>
    </div>
    <div class="strate">

        <?php component::form('text', 'Label', 'name', true, null, 'Remplir ce champs'); ?>

    </div>
    <div class="strate">

        <?php component::navanchor([
            [
                "anchor" => "section-1",
                "name" => "Section 1"
            ],
            [
                "anchor" => "section-2",
                "name" => "Section 2"
            ],
            [
                "anchor" => "section-3",
                "name" => "Section 3"
            ]
        ], null, null, 'Table des matières'); ?>
    </div>
    <div class="strate">

        <?php component::picto('youtube'); ?>
    </div>
    <div class="strate">

        <?php component::select([
            [
                "name" => "Option 1",
                "value" => "option-1"
            ],
            [
                "name" => "Option 2",
                "value" => "option-2",
                "selected" => true
            ],
            [
                "name" => "Option 3",
                "value" => "option-3"
            ],
            [
                "name" => "Option desactivee",
                "value" => "option-disabled",
                "disabled" => true
            ]
        ], 'Mon select', 'mon-select'); ?>
    </div>
    <div class="strate">

        <?php component::select_custom([
            [
                "name" => "Option 1",
                "value" => "option-1"
            ],
            [
                "name" => "Option 2",
                "value" => "option-2",
                "selected" => true
            ],
            [
                "name" => "Option 3",
                "value" => "option-3"
            ]
        ], 'Mon label'); ?>
    </div>

    <div class="strate">
        <?php component::icon('email', 40, 40); ?>
    </div>

    <div class="strate">

        <?php component::shares(['email', 'copy', 'facebook', 'x', 'whatsapp']); ?>
    </div>

    <div class="strate">
        <?php component::video('https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Never Gonna Give You Up', 460); ?>
    </div>

    <div class="strate">
        <?php component::quote([
            "quote" => "Une citation courte permet de faire respirer une page.",
            "author" => "Jane Doe",
            "role" => "Directrice generale"
        ]); ?>
    </div>

    <div class="strate">
        <?php component::table([
            [
                "Purchase",
                "Location",
                "Date",
                "Evaluation",
                "Cost (€)"
            ],
            [
                "Haircut",
                "Hairdresser",
                "12/09",
                "Great idea",
                "30"
            ],
            [
                "Lasagna",
                "Restaurant",
                "12/09",
                "Regrets",
                "18"
            ]
        ]); ?>
    </div>

    <div class="strate">
        <?php component::list([
            [
                "title" => "Lorem ipsum dolor sit amet",
                "images" => [
                    "desktop" => THEME_ASSETS . "img/63-1400x1024.jpg"
                ]
            ],
            [
                "title" => "Lorem ipsum dolor sit amet",
                "images" => [
                    "desktop" => THEME_ASSETS . "img/63-1400x1024.jpg"
                ]
            ]
        ], 'news'); ?>
    </div>
    <div class="strate">
        <?php component::image(THEME_ASSETS . "img/63-1400x1024.jpg", 'full'); ?>
    </div>

    <div class="strate">
        <?php component::tag('Lorem ipsum dolor sit amet'); ?>
    </div>
    <div class="strate">
        <?php component::tooltip('Information', 'Texte d’aide court, affiché au survol et au focus.'); ?>
    </div>

    <div class="strate">
        <?php component::header([
            "title" => "Lorem ipsum dolor",
            "text" => "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>",
            "link" => [
                "title" => "En savoir plus",
                "url" => "#",
                "target" => ""
            ]
        ]); ?>
    </div>

    <div class="strate">
        <?php component::search('Rechercher', 'Que recherchez-vous ?', 'Rechercher'); ?>
    </div>

    <div class="strate">
        <?php component::autocomplete([
            [
                "name" => "France",
                "value" => "fr"
            ],
            [
                "name" => "Allemagne",
                "value" => "de"
            ],
            [
                "name" => "Espagne",
                "value" => "es"
            ]
        ], ""); ?>
    </div>
</main>

<?php get_template_part('footer'); ?>
