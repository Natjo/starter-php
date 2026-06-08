<?php
$args = normalize_args($args ?? null);

$total = isset($args["total"]) && is_numeric($args["total"]) ? max(1, (int) $args["total"]) : 1;
$current = isset($args["current"]) && is_numeric($args["current"]) ? (int) $args["current"] : 1;
$current = min(max(1, $current), $total);
$range = isset($args["range"]) && is_numeric($args["range"]) ? max(0, (int) $args["range"]) : 2;
$url_pattern = isset($args["url"]) && is_scalar($args["url"])
    ? trim((string) $args["url"])
    : "?page={page}";
$endpoint = isset($args["endpoint"]) && is_scalar($args["endpoint"])
    ? trim((string) $args["endpoint"])
    : "";
$target = isset($args["target"]) && is_scalar($args["target"])
    ? trim((string) $args["target"])
    : "";
$page_param = isset($args["page_param"]) && is_scalar($args["page_param"])
    ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $args["page_param"])
    : "page";
$aria_label = isset($args["aria_label"]) && is_scalar($args["aria_label"])
    ? trim((string) $args["aria_label"])
    : "Pagination";
$classes = component::classes("pagination", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
$ajax = !empty($args["ajax"]) && $endpoint !== "" && $target !== "";

if ($total <= 1) return;

$page_url = static fn(int $page): string => str_replace("{page}", (string) $page, $url_pattern);
$pages = [1];
$start = max(2, $current - $range);
$end = min($total - 1, $current + $range);

if ($start > 2) {
    $pages[] = "ellipsis";
}

for ($page = $start; $page <= $end; $page++) {
    $pages[] = $page;
}

if ($end < $total - 1) {
    $pages[] = "ellipsis";
}

$pages[] = $total;
?>

<nav
    class="<?= $classes ?>"
    aria-label="<?= esc_attr($aria_label) ?>"
    <?= $ajax ? 'data-module="components/pagination"' : "" ?>
    <?= $ajax ? 'data-endpoint="' . esc_url($endpoint) . '"' : "" ?>
    <?= $ajax ? 'data-target="' . esc_attr($target) . '"' : "" ?>
    <?= $ajax ? 'data-page-param="' . esc_attr($page_param !== "" ? $page_param : "page") . '"' : "" ?>
    <?= $ajax ? 'data-context="@visible true"' : "" ?>
    <?= $attributes ?>>
    <a
        class="pagination-control pagination-prev"
        href="<?= esc_url($page_url(max(1, $current - 1))) ?>"
        <?= $current === 1 ? 'aria-disabled="true" tabindex="-1"' : 'rel="prev"' ?>>
        <span aria-hidden="true">&larr;</span>
        <span>Précédent</span>
    </a>

    <ol class="pagination-pages">
        <?php foreach ($pages as $page) : ?>
            <li>
                <?php if ($page === "ellipsis") : ?>
                    <span class="pagination-ellipsis" aria-hidden="true">&hellip;</span>
                <?php elseif ($page === $current) : ?>
                    <a
                        class="pagination-page is-current"
                        href="<?= esc_url($page_url((int) $page)) ?>"
                        aria-label="Page <?= (int) $page ?>"
                        aria-current="page">
                        <?= (int) $page ?>
                    </a>
                <?php else : ?>
                    <a
                        class="pagination-page"
                        href="<?= esc_url($page_url((int) $page)) ?>"
                        aria-label="Aller à la page <?= (int) $page ?>">
                        <?= (int) $page ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <a
        class="pagination-control pagination-next"
        href="<?= esc_url($page_url(min($total, $current + 1))) ?>"
        <?= $current === $total ? 'aria-disabled="true" tabindex="-1"' : 'rel="next"' ?>>
        <span>Suivant</span>
        <span aria-hidden="true">&rarr;</span>
    </a>

    <span class="sr-only" role="status" aria-live="polite" data-pagination-status></span>
</nav>
