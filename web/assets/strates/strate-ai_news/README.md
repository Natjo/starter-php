# Strate AI news

Cette strate sert à remontée les news fraiches sur l'AI dans les sites spécialisées.


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

    "suptitle" => "AI news",
    "title" => "Stay informed",
    "items" => [
        [
            "source" => "The Verge",
            "date" => "14 juin 2026",
            "datetime" => "2026-06-14",
            "title" => "Titre de l’article",
            "text" => "<p>Résumé de l’article.</p>",
            "link" => [
                "url" => "https://...",
                "target" => "_blank",
            ],
        ],
    ],
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |
| Items     | `items`    | -               |           - |             - |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::list()`
- `card-news`
