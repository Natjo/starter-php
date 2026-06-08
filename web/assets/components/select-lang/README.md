# Select lang

Affiche un sélecteur compact qui redirige vers la version choisie de la page.

```php
component::select_lang([
    ["code" => "fr", "label" => "FR", "url" => "/fr/", "current" => true],
    ["code" => "en", "label" => "EN", "url" => "/en/"],
]);
```

## Arguments

- `languages` ou `args` : liste des langues.
- `label` : libellé accessible du sélecteur.
- `name` : nom du champ.
- `classes` et `attributes` : classes et attributs HTML supplémentaires.

Chaque langue accepte `code`, `label`, `url`, `current` ou `selected`, et `disabled`.
