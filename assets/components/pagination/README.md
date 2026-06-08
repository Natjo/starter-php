# Pagination

Pagination générée par PHP avec amélioration AJAX facultative. Les liens restent
fonctionnels sans JavaScript et PHP conserve la logique des pages et des ellipses.

```php
component::pagination([
    "current" => 8,
    "total" => 20,
    "range" => 2,
    "url" => "/actualites/?page={page}",
    "ajax" => true,
    "endpoint" => THEME_ASSETS . "components/pagination/pagination-ajax.php",
    "target" => "news-results",
]);
```

## Arguments

- `current`, `total` : page courante et nombre total de pages.
- `range` : nombre de pages visibles avant et après la page courante.
- `url` : URL PHP contenant le marqueur `{page}`.
- `ajax` : active l'amélioration AJAX.
- `endpoint` : URL retournant `results` et `pagination` en JSON.
- `target` : identifiant du conteneur de résultats à remplacer.
- `page_param` : paramètre utilisé dans l'URL publique.
- `aria_label`, `classes`, `attributes` : personnalisation du composant.

`pagination-ajax.php` contient uniquement des données fictives de démonstration.
