<?php
$framesBase = THEME_UPLOADS . "footer-frames/";
$framesCount = 46;
?>

<footer id="footer" role="contentinfo" data-module="common/footer" data-context="@visible true">
    <canvas
        aria-hidden="true"
        data-frames-base="<?= esc_url($framesBase) ?>"
        data-frames-count="<?= (int) $framesCount ?>">
    </canvas>

    <div class="footer-content">
        <?php component::icon("star-stroke", 38, 38); ?>

        <?php component::text("Neither fully human<br>nor entirely machine"); ?>

        <nav aria-label="">
            <ul>
                <li><a href="">Mentions legales</a></li>
                <li><a href="">Paramétrer les cookies</a></li>
            </ul>
        </nav>

        <small>©Richard 2026</small>
    </div>

    <?php component::icon("lonsdale_partners", 1320, 85, "lonsdale_partners"); ?>
</footer>