<?php
declare(strict_types=1);

ob_start();
?>
<section class="admin-panel">
    <h2>Ux</h2>
    <p>Section en preparation.</p>
</section>
<?php

return [
    'title' => 'Admin - Ux',
    'heading' => 'Ux',
    'intro' => 'Espace reserve aux futurs outils et suivis UX.',
    'content' => (string) ob_get_clean(),
];
