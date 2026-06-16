# Strate Key people



## Options
```php
$options = [
    "menu-name" => "People"
]
```

## Data Contract
```php
[
    ...$options,

    "suptitle" => "Key people",
    "title" => "Your go-to experts for <strong>AI questions</strong>, guidance, and collaboration across the agency.",
    "placeholder" => 74,
    "items" => [
        [
            "name" => "Sophie Marchand",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" => 74,
        ],
        [
            "name" => "Thomas Durand",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" => 74,
        ],
        [
            "name" => "Léa fontaine",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" => 74,
        ],
        [
            "name" => "Marc Lefèvre",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" =>74,
        ],
        [
            "name" => "Camille Bernard",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" =>74,
        ],
        [
            "name" => "Antoine Morel",
            "function" => "Senior Prompt Engineer",
            "from" => "AI Strategy & Governance",
            "shares" => [
                "linkedin" => "url",
                "email" => "email"
            ],
            "image" => 74,
        ]
    ]
]
```

## Acf
| Nom         | id            | Type de champ   | Obligatoire | administrable | commentaire |
| ----------- | ------------- | --------------- | ----------: | ------------: | ----------: |
| Sur titre   | `suptitle`    | `wysiwyg light` |         Oui |           Oui |             |
| Titre       | `title`       | `wysiwyg light` |         Oui |           Oui |             |
| Placeholder | `placeholder` | `image`         |         Oui |           Oui |             |
| Images      | `items`       | `image`         |         Oui |           Oui |          id |



| $items   | id         | Type de champ | Obligatoire | administrable | commentaire |
| -------- | ---------- | ------------- | ----------: | ------------: | ----------: |
| Nom      | `name`     | `text`        |         Oui |           Oui |             |
| Fonction | `function` | `text`        |         Oui |           Oui |             |
| From     | `from`     | `text`        |         Oui |           Oui |             |
| Shares   | `shares`   | `groupe`      |         Oui |           Oui |             |
| Image    | `image`    | `image`       |         Oui |           Oui |          id |



| $share   | id      | Type de champ | Obligatoire | administrable |
| -------- | ------- | ------------- | ----------: | ------------: |
| Linkedin | `name`  | `url`         |         Oui |           Oui |
| Email    | `email` | `email`       |         Oui |           Oui |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`
- `component::picture()`

