<?php
get_template_part('assets/common/header/header');

$args = [
    "brand" => "Mon projet",
    "pages" => [
        [
            "title" => "Accueil",
            "url" => "/",
        ],
        [
            "title" => "Contact",
            "url" => "/contact",
        ],
    ],
];
get_template_part('assets/common/header-nav/header-nav', null, $args);
?>

<main>
    <?php
    hero("homepage", [
        "title" => "lorem",
        "text" => "lorem"
    ]);
    ?>

    <?php
    strate("text",  [
        "title" => "lorem",
        "text" => "lorem",
        "link" => [
            "title" => "See more",
            "url" => "/",
        ],
        "options" => [
            "container" => [],
            "margin" => [
                "top" => "",
                "bottom" => "lg"
            ],
            "background" => [
                "hasbackground" => true,
                "color" => "color-1",
                "padding" => [
                    "top" => "lg",
                    "bottom" => ""
                ]
            ]
        ]
    ]);
    ?>

    <?php
    strate("text-image",  [
        "title" => "lorem",
        "text" => "lorem",
        "link" => [
            "title" => "See more",
            "url" => "/",
        ],
        "images" => [
            "desktop" => THEME_ASSETS . "img/63-1400x1024.jpg"
        ]
    ]);
    ?>

    <?php
    strate("quote",  [
        "title" => "lorem",
        "text" => "lorem",
        "quote" => [
            "text" => "sds",
            "author" => "",
            "role" => ""
        ],

    ]);
    ?>

    <?php
    strate("slider",  [
        "title" => "lorem",
        "text" => "lorem",
        "items" => [
            [
                "title" => "test",
                "text" => "popo"
            ],
            [
                "title" => "test",
                "text" => "popo"
            ],
            [
                "title" => "test",
                "text" => "popo"
            ]
        ]
    ]);
    ?>


    <?php
    strate("accordion",  [
        "title" => "lorem",
        "text" => "lorem",
        "items" => [
            [
                "title" => "test",
                "text" => "popo"
            ],
            [
                "title" => "test",
                "text" => "popo"
            ],
            [
                "title" => "test",
                "text" => "popo"
            ]
        ]
    ]);
    ?>

    <?php component::badge("ret") ?>

    <?php component::dialog("<p>lorem</p>", ['btn', 'Open dialog', null]); ?>

    <?php component::form('text', 'Label', 'name', true, null, 'Remplir ce champs'); ?>

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

    <?php component::picto('youtube'); ?>

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


</main>

<?php get_template_part('assets/common/footer/footer'); ?>