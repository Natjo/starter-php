<footer id="footer">
    footer
    <?php

    nav_menu([
        'theme_location' => 'menu-footer',
    ]);
    ?>

</footer>

<script type="module" src="<?= esc_url(dist_versioned_asset_url('app.js')) ?>"></script>
</body>

</html>