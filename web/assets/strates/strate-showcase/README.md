# Strate Showcase



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
    
    "suptitle" => "Showcase",
    "title" => "Made by human powered <strong>by AI</strong>.",
    "items" => [
        [
            [
                "image" => THEME_UPLOADS . "showcase-2.jpg",
            ],
            [
                "image" => THEME_UPLOADS . "showcase-5.jpg",
            ],
            [
                "image" => THEME_UPLOADS . "showcase-3.jpg",
            ],

        ],
        [
            [
                "image" => THEME_UPLOADS . "showcase-1.jpg",
            ],
            [
                "image" => THEME_UPLOADS . "showcase-4.jpg",
            ],
        ],
        [
            [
                "image" => THEME_UPLOADS . "showcase-4.jpg",
            ],
            [
                "image" => THEME_UPLOADS . "showcase-6.jpg",
            ],
        ],

        [
            [
                "isVideo" => true,
                "video" => THEME_UPLOADS . "showcase-1.mp4"
            ],
            [
                "image" => THEME_UPLOADS . "showcase-2.jpg",
            ],
            [
                "image" => THEME_UPLOADS . "showcase-5.jpg",
            ],
        ]
    ]
]
```

## Acf
| Nom | id  | Type de champ | Obligatoire | administrable | remarque |
| --- | --- | ------------- | ----------: | ------------: ||
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui ||
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui ||
| Images    | `items`    | ``              |         Oui |           Oui | Il faut 4 colonnes|


| $items | id      | Type de champ | Obligatoire | administrable |
| ------ | ------- | ------------- | ----------: | ------------: |
| Image  | `image` | `image`       |         Oui |           Oui |




##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

