<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . "/starter/config.php";
require_once STARTER_ROOT . "/method.php";
require_once STARTER_ROOT . "/wp-compat.php";
require_once STARTER_ROOT . "/functions.php";
require_once STARTER_ROOT . "/components.php";
require_once __DIR__ . "/search-data.php";

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

$state = search_state($_GET);
$result = search_results($state);
$target = isset($_GET["target"]) && is_scalar($_GET["target"])
    ? sanitize_html_class((string) $_GET["target"])
    : "search-results";
$endpoint = $_SERVER["SCRIPT_NAME"] ?? "/search/search-ajax.php";

echo json_encode([
    "page" => $result["page"],
    "total" => $result["total"],
    "results" => search_render_results($result),
    "pagination" => search_render_pagination($state, $result, $endpoint, $target),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
