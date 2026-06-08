<?php
declare(strict_types=1);

ob_start();
?>
<section class="admin-empty">
    <h2>Page vide pour l instant</h2>
    <p>Cette section accueillera plus tard les outils SEO : metas, indexation, donnees structurees et signaux de contenu.</p>
</section>
<?php

return [
    'title' => 'Admin - SEO',
    'heading' => 'SEO',
    'intro' => 'Base de travail pour piloter les sujets de referencement dans une interface admin dediee.',
    'content' => (string) ob_get_clean(),
];
