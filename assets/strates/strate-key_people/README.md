# Strate Key people



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

    "suptitle" => "Key people",
    "title" => "Your go-to experts for <strong>AI questions</strong>, guidance, and collaboration across the agency.",
    "placeholder" => THEME_UPLOADS . "people-0.jpg",
    "items" => [
        [
            "name" => "Sophie Marchand",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-1.jpg"
        ],
        [
            "name" => "Thomas Durand",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-2.jpg"
        ],
        [
            "name" => "Léa fontaine",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-3.png"
        ],
        [
            "name" => "Marc Lefèvre",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-4.png"
        ],
        [
            "name" => "Camille Bernard",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-5.png"
        ],
        [
            "name" => "Antoine Morel",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => ["linkedin", "email"],
            "image" => THEME_UPLOADS . "people-6.jpg"
        ]
    ]
]
```

## Acf
| Nom         | id            | Type de champ   | Obligatoire | administrable |
| ----------- | ------------- | --------------- | ----------: | ------------: |
| Sur titre   | `suptitle`    | `wysiwyg light` |         Oui |           Oui |
| Titre       | `title`       | `wysiwyg light` |         Oui |           Oui |
| Placeholder | `placeholder` | `image`         |         Oui |           Oui |
| Images      | `items`       | `image`         |         Oui |           Oui |


| $items   | id         | Type de champ | Obligatoire | administrable |
| -------- | ---------- | ------------- | ----------: | ------------: |
| Nom      | `name`     | `text`        |         Oui |           Oui |
| Fonction | `function` | `text`        |         Oui |           Oui |
| From     | `from`     | `text`        |         Oui |           Oui |
| Shares   | `shares`   | ``            |         Oui |           Oui |
| Image    | `image`    | `image`       |         Oui |           Oui |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`
- `component::picture()`

