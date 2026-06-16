# Strate Toolkit



## Options
```php
$options = [
    "menu-name" => "Toolkit"
]
```

## Data Contract
```php
[
    ...$options,

    "suptitle" => "<strong>Toolkit</strong> : creative solutions",
    "title" => "Our curated catalog of AI tools approved for agency use. Each solution has been vetted for quality, security, and creative value.",
    "items" => [
        [
            "suptitle" => "Image generation",
            "icon" => 56,
            "title" => "Midjourney",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
            "usage" => [
                "title" => "How to use",
                "text" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
            ]
          
        ],
        [
            "suptitle" => "Text & strategy",
           "icon" => 56,
            "title" => "ChatGPT",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
              "usage" => [
                "title" => "How to use",
                "text" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
            ]
        ],
        [
            "suptitle" => "Image generation",
            "icon" => 56,
            "title" => "DALL·E 3",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
                 "usage" => [
                "title" => "How to use",
                "text" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
            ]
        ]
    ] 
]
```

## Acf
| Nom       | id         | Type de champ        | Obligatoire | administrable | remarque |
| --------- | ---------- | -------------------- | ----------: | ------------: | -------: |
| Sur titre | `suptitle` | `wysiwyg light`      |         Oui |           Oui |          |
| Titre     | `title`    | `wysiwyg light`      |         Oui |           Oui |          |
| Items     | `items`    | `repeteur`  - $items |         Oui |           Oui |          |


| $items    | id         | Type de champ      | Obligatoire | administrable |    remarque |
| --------- | ---------- | ------------------ | ----------: | ------------: | ----------: |
| Sur titre | `suptitle` | `wysiwyg light`    |         Oui |           Oui |             |
| Icon      | `icon`     | `image`            |         Oui |           Oui | list d'icon |
| Title     | `title`    | `text`             |         Oui |           Oui |             |
| Text      | `text`     | `wysiwyg`          |         Oui |           Oui |             |
| Usage     | `usage`    | `groupe`  - $usage |         Oui |           Oui |             |


| $usage | id      | Type de champ  | Obligatoire | administrable | remarque |
| ------ | ------- | -------------- | ----------: | ------------: | -------: |
| Titre  | `title` | `text`         |         Oui |           Oui |          |
| Texte  | `text`  | `zone de text` |         Oui |           Oui |          |

##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

