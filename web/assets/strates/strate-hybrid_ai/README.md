# Strate Hybrid AI



## Options
```php
$options = [
    "menu-name" => ""
]
```

## Data Contract
```php
[
    ...$options,

    "title" => "Where human intelligence meets ai power. ", 
    "text" => "By lonsdale.",
    "video" => 66
]
```

## Acf
| Nom   | id      | Type de champ   | Obligatoire | administrable | commentaire |
| ----- | ------- | --------------- | ----------: | ------------: | ----------: |
| Titre | `title` | `wysiwyg light` |         Oui |           Oui |             |
| Text  | `text`  | `text`          |           - |           Oui |             |
| Vidéo | `video` | `fichier`       |           - |           Oui |          id |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

