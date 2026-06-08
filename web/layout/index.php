<?php common('header-nav'); ?>

<?php page_assest("layout") ?>

<main id="main">
    <?php
    hero("page", [
        "title" => "Layout"
    ]);
    ?>


    <div class="layout-sidebar">
        <div class="sidebar">
            sidebar
        </div>

        <div class="page-content">
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
        </div>
    </div>
</main>

<?php common('footer'); ?>
