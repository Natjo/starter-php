<?php
declare(strict_types=1);

ob_start();
?>
<section class="admin-empty">
    <h2>Page vide pour l instant</h2>
    <p>Cette section accueillera plus tard les audits d accessibilite, les checks automatiques et les points de vigilance a corriger.</p>
</section>
<?php

return [
    'title' => 'Admin - Accessibilite',
    'heading' => 'Accessibilite',
    'intro' => 'Base de travail pour suivre l accessibilite du site sans melanger cette UI avec le design public.',
    'content' => (string) ob_get_clean(),
];
