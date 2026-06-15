# Select lang

Affiche un bouton compact qui ouvre une liste de liens vers les langues disponibles.

```php
component::select_lang([
    ["code" => "fr", "label" => "FR", "url" => "/fr/", "current" => true],
    ["code" => "en", "label" => "EN", "url" => "/en/"],
]);
```

## Arguments

- `languages` ou `args` : liste des langues.
- `label` : libellé accessible du sélecteur.
- `classes` et `attributes` : classes et attributs HTML supplémentaires.

Chaque langue accepte `code`, `label`, `url`, `current` ou `selected`, et `disabled`.

Le composant n'utilise pas de `<select>`. La langue courante est affichée dans le bouton et marquée avec `aria-current="page"` dans la liste.
