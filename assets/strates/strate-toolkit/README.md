# Strate Toolkit



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

    "suptitle" => "<strong>Toolkit</strong> : creative solutions",
    "title" => "Our curated catalog of AI tools approved for agency use. Each solution has been vetted for quality, security, and creative value.",
    "items" => [
        [
            "suptitle" => "Image generation",
            "icon" => "midjourney",
            "title" => "Midjourney",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
            "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
        ],
        [
            "suptitle" => "Text & strategy",
            "icon" => "chatgpt",
            "title" => "ChatGPT",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
            "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
        ],
        [
            "suptitle" => "Image generation",
            "icon" => "chatgpt",
            "title" => "DALL·E 3",
            "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
            "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
        ]
    ] 
]
```

## Acf
| Nom | id  | Type de champ | Obligatoire | administrable | remarque |
| --- | --- | ------------- | ----------: | ------------: |------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui ||
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui ||
| Items    | `items`    | ``              |         Oui |           Oui ||


| $items | id  | Type de champ | Obligatoire | administrable | remarque |
| ------ | --- | ------------- | ----------: | ------------: |------------: |
| Sur titre | `image` | `wysiwyg light` |         Oui |           Oui ||
| Icon      | ``      | ``              |         Oui |           Oui | list d'icon |
| Title     | `image` | `wysiwyg light` |         Oui |           Oui ||
| Text      | `image` | `wysiwyg`       |         Oui |           Oui ||
| Usage     | `image` | `wysiwyg`       |         Oui |           Oui ||



##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

