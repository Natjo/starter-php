# Strate Hybrid AI



## Options
```php
$options = [
    "id" => "strate-id"
]
```

## Data Contract
```php
[
    ...$options,

    "title" => "Where human intelligence meets ai power. ", 
    "text" => "By lonsdale.",
    "video" => ""
]
```

## Acf
| Nom   | id      | Type de champ   | Obligatoire | administrable |
| ----- | ------- | --------------- | ----------: | ------------: |
| Titre | `title` | `wysiwyg light` |         Oui |           Oui |
| Text  | `text`  | `text`          |           - |             - |
| Vidéo | `video` | `video`         |           - |             - |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

