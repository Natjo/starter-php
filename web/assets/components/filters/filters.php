<?php
$args = normalize_args($args ?? null);

$action = isset($args["action"]) && is_scalar($args["action"]) ? trim((string) $args["action"]) : "/";
$endpoint = isset($args["endpoint"]) && is_scalar($args["endpoint"]) ? trim((string) $args["endpoint"]) : "";
$target = isset($args["target"]) && is_scalar($args["target"]) ? trim((string) $args["target"]) : "";
$pagination_target = isset($args["pagination_target"]) && is_scalar($args["pagination_target"])
    ? trim((string) $args["pagination_target"])
    : "";
$query = isset($args["query"]) && is_array($args["query"]) ? $args["query"] : [];
$filters = isset($args["filters"]) && is_array($args["filters"]) ? $args["filters"] : [];
$submit_label = isset($args["submit_label"]) && is_scalar($args["submit_label"])
    ? trim((string) $args["submit_label"])
    : "Filtrer";
$classes = component::classes("filters", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

if ($endpoint === "" || $target === "" || $pagination_target === "") return;

$query_name = isset($query["name"]) && is_scalar($query["name"]) ? trim((string) $query["name"]) : "q";
$filter_name = isset($args["filter_name"]) && is_scalar($args["filter_name"])
    ? trim((string) $args["filter_name"])
    : "type[]";
$page_param = isset($args["page_param"]) && is_scalar($args["page_param"])
    ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $args["page_param"])
    : "page";
?>

<form
    class="<?= $classes ?>"
    action="<?= esc_url($action) ?>"
    method="get"
    role="search"
    data-module="components/filters"
    data-endpoint="<?= esc_url($endpoint) ?>"
    data-target="<?= esc_attr($target) ?>"
    data-pagination-target="<?= esc_attr($pagination_target) ?>"
    data-query-name="<?= esc_attr($query_name) ?>"
    data-filter-name="<?= esc_attr($filter_name) ?>"
    data-page-param="<?= esc_attr($page_param !== "" ? $page_param : "page") ?>"<?= $attributes ?>>
    <?php if (!empty($query)) : ?>
        <?php form([
            "type" => $query["type"] ?? "text",
            "label" => $query["label"] ?? "Rechercher",
            "name" => $query_name,
            "placeholder" => $query["placeholder"] ?? "",
            "value" => $query["value"] ?? "",
        ]); ?>
    <?php endif; ?>

    <?php if (!empty($filters)) : ?>
        <?php form([
            "type" => "checkboxes",
            "label" => $args["filters_label"] ?? "Filtres",
            "name" => rtrim($filter_name, "[]"),
            "options" => array_map(static function ($filter) use ($filter_name): array {
                $filter = is_array($filter) ? $filter : [];
                $filter["name"] = $filter_name;

                return $filter;
            }, $filters),
        ]); ?>
    <?php endif; ?>

    <?php component::btn([
        "name" => $submit_label,
        "attributes" => ["type" => "submit"],
    ]); ?>
</form>
