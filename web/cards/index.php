<?php common('header-nav'); ?>

<main id="main">
    <?php
    hero("page", [
        "title" => "Cards"
    ]);
    ?>

    <section class="strate">
        <?php card("news", [
            "title" => "Une actualité exemple",
            "text" => "Un court résumé pour vérifier le rendu de la card news avec du contenu fake.",
            "images" => THEME_ASSETS . "img/63-1400x1024.jpg",
            "link" => [
                "title" => "Lire l'actualité",
                "url" => "#",
            ],
        ]) ?>
    </section>


</main>

<?php common('footer'); ?>