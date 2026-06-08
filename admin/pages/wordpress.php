<?php
declare(strict_types=1);

ob_start();
?>
<section class="admin-panel">
    <h2>WordPress</h2>
    <p>Section en preparation.</p>
</section>
<?php

return [
    'title' => 'Admin - WordPress',
    'heading' => 'WordPress',
    'intro' => 'Espace reserve a la configuration et au suivi de l integration WordPress.',
    'content' => (string) ob_get_clean(),
];
