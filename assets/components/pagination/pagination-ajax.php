<?php
declare(strict_types=1);

$directory = __DIR__;
$projectRoot = "";

for ($level = 0; $level < 6; $level++) {
    if (is_file($directory . "/starter/config.php")) {
        $projectRoot = $directory;
        break;
    }

    $parent = dirname($directory);
    if ($parent === $directory) break;
    $directory = $parent;
}

if ($projectRoot === "") {
    http_response_code(500);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode(["error" => "Starter introuvable"]);
    exit;
}

require_once $projectRoot . "/starter/config.php";
require_once STARTER_ROOT . "/method.php";
require_once STARTER_ROOT . "/wp-compat.php";
require_once STARTER_ROOT . "/functions.php";
require_once STARTER_ROOT . "/components.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? max(1, (int) $_GET["page"]) : 1;
$target = isset($_GET["target"]) && is_scalar($_GET["target"])
    ? sanitize_html_class((string) $_GET["target"])
    : "pagination-demo-results";
$pageParam = isset($_GET["page_param"]) && is_scalar($_GET["page_param"])
    ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $_GET["page_param"])
    : "pagination_page";
$perPage = 4;
$totalItems = 48;
$totalPages = (int) ceil($totalItems / $perPage);
$page = min($page, $totalPages);
$start = (($page - 1) * $perPage) + 1;
$end = min($totalItems, $start + $perPage - 1);

ob_start();
?>
<div class="pagination-demo-grid">
    <?php for ($item = $start; $item <= $end; $item++) : ?>
        <article class="pagination-demo-item">
            <span>Article <?= $item ?></span>
            <strong>Contenu fictif de la page <?= $page ?></strong>
        </article>
    <?php endfor; ?>
</div>
<?php
$results = (string) ob_get_clean();

ob_start();
component::pagination([
    "current" => $page,
    "total" => $totalPages,
    "range" => 2,
    "url" => "/components/?" . $pageParam . "={page}",
    "ajax" => true,
    "endpoint" => $_SERVER["SCRIPT_NAME"] ?? "",
    "target" => $target,
    "page_param" => $pageParam,
]);
$pagination = (string) ob_get_clean();

echo json_encode([
    "page" => $page,
    "results" => $results,
    "pagination" => $pagination,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
