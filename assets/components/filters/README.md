# Filters

Formulaire de filtres avec amélioration AJAX, remplacement des résultats et
mise à jour de la pagination. Sans JavaScript, le formulaire GET reste utilisable.

```php
component::filters([
    "action" => "/search/",
    "endpoint" => "/search/search-ajax.php",
    "target" => "search-results",
    "pagination_target" => "search-pagination",
    "page_param" => "search_page",
    "query" => [
        "label" => "Rechercher",
        "name" => "q",
        "value" => "",
    ],
    "filters" => [
        ["label" => "Cards", "value" => "cards", "checked" => true],
    ],
]);
```
