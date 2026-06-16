# Strate Showcase



## Options
```php
$options = [
    "menu-name" => "Showcase"
]
```

## Data Contract
```php
[
    ...$options,
    
    "suptitle" => "Showcase",
    "title" => "Made by human powered <strong>by AI</strong>.",
    "items" => [
        [
            "items" => [
                [
                    "image" => 33
                ],
                [
                    "image" => 33
                ],
                [
                    "image" => 33
                ],
            ]

        ],
        [
            "items" => [
                [
                    "image" => 33
                ],
                [
                    "image" =>33
                ],
            ]
        ],
        [
            "items" => [
                [
                    "image" => 33
                ],
                [
                    "image" => 33
                ],
            ]
        ],
        [
            "items" => [
                [
                    "isVideo" => true,
                    "video" => 36
                ],
                [
                    "image" => 33
                ],
                [
                    "image" => 33
                ],
            ]
        ]
    ]
]
```

## Acf
| Nom       | id         | Type de champ            | Obligatoire | administrable |        commentaire |
| --------- | ---------- | ------------------------ | ----------: | ------------: | -----------------: |
| Sur titre | `suptitle` | `wysiwyg light`          |         Oui |           Oui |                    |
| Titre     | `title`    | `wysiwyg light`          |         Oui |           Oui |                    |
| Colonnes  | `items`    | `repeteur`  - $items_col |         Oui |           Oui | Il faut 4 colonnes |


| $items_col    | id        | Type de champ             | Obligatoire | administrable | commentaire |
| ------------- | --------- | ------------------------- | ----------: | ------------: | ----------: |
| Est une video | `isVideo` | `vrai/faux`  - $items_row |         Oui |           Oui |             |
| Image         | `image`   | `image`                   |         Oui |           Oui |          id |
| Video         | `video`   | `fichier`                 |         Oui |           Oui |          id |




##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

