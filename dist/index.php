<?php common('header-nav'); ?>

<main id="main">

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
</main>
  

<?php common('footer'); ?>
