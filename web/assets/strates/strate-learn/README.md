# Strate Learn



## Options
```php
$options = [
    "menu-name" => "Training"
]
```

## Data Contract
```php
[
    ...$options,

    "suptitle" => "Learn",
    "title" => "Best practices & training",
    "items" => [
        [
            "suptitle" => "Training",
            "title" => "AI FUNDAMENTALS WORKSHOP",
            "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
            "icon" => "icon-video",
            "link" => [
                "title" => "",
                "url" => "",
                "target" => "_blank",
            ]
        ],
        [
            "suptitle" => "Training",
            "title" => "AI FUNDAMENTALS WORKSHOP",
             "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
            "icon" => "icon-training",
            "link" => [
                "title" => "",
                "url" => "",
                "target" => "_blank",
            ]
        ],
        [
            "suptitle" => "Training",
            "title" => "AI FUNDAMENTALS WORKSHOP",
            "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
            "icon" => "icon-video",
            "link" => [
                "title" => "",
                "url" => "",
                "target" => "_blank",
            ]
        ],
        [
            "suptitle" => "Training",
            "title" => "AI FUNDAMENTALS WORKSHOP",
            "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
            "link" => [
                "title" => "",
                "url" => "",
                "target" => "_blank",
            ]
        ],
    ]
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |
| Items     | `items`    | `repeteur`      |         Oui |           Oui |


| $items    | id         | Type de champ   | Obligatoire | administrable |                                             commmentaire |
| --------- | ---------- | --------------- | ----------: | ------------: | :------------------------------------------------------- |
| Sup title | `suptitle` | `wysiwyg light` |         Oui |           Oui |                                                          |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |                                                          |
| Text      | `text`     | `wysiwyg`       |         Oui |           Oui |                                                          |
| Icon      | `icon`     | `select`        |         Oui |           Oui | "" : Aucune <br> icon-training : Training,<br>  icon-video : Video |
| Lien      | `link`     | `link`          |         Oui |           Oui |                                                          |



##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

