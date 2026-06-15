# Strate Platform



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
    
    "suptitle" => "+ Platform",
    "title" => "Dust",
    "text" => "<p>Dust is our central AI platform for building and deploying intelligent assistants. Access custom agents, connect your data sources, and supercharge your creative process.</p>",
    "link" => [
        "title" => "Acees dust",
        "url" => "#",
        "target" => "_blank",
    ],
    "items" => [
        [
            "icon" => "bdd",
            "title" => "Conversational AI assistants tailored to your workflows",
            "text" => "<p>Browse available assistants or create your own to match the way your teams already work.</p>",
        ],
        [
            "icon" => "ai",
            "title" => "Connect your knowledge base for contextual answers",
            "text" => "<p>Connect relevant data sources (Drive, Notion, Slack) so assistants answer with your real context.</p>",
        ],
        [
            "icon" => "chat",
            "title" => "Build custom AI agents without code",
            "text" => "<p>Start conversing and building workflows in minutes, no engineering resources required.</p>",
        ],
    ],
    "platforms" => [
        [
            "title" => "Log in at <strong>dust.tt</strong> with your agency credentials",
            "image" => THEME_UPLOADS . "platform.jpg"
        ],
        [
            "title" => "Browse available assistants or create your own",
            "image" => THEME_UPLOADS . "platform.jpg"
        ],
        [
            "title" => "Connect relevant data sources (Drive, Notion, Slack)",
            "image" => THEME_UPLOADS . "platform.jpg"
        ],
        [
            "title" => "Start conversing and building workflows",
            "image" => THEME_UPLOADS . "platform.jpg"
        ]
    ]
]
```

## Acf
| Nom       | id          | Type de champ   | Obligatoire | administrable |
| --------- | ----------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle`  | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`     | `wysiwyg light` |         Oui |           Oui |
| Items     | `items`     | ``              |         Oui |           Oui |
| Platfome  | `platforms` | ``              |         Oui |           Oui |


| $items | id      | Type de champ   | Obligatoire | administrable |
| ------ | ------- | --------------- | ----------: | ------------: |
| Icon   | `icon`  | `select`        |         Oui |           Oui |
| Titre  | `title` | `wysiwyg light` |         Oui |           Oui |
| Text   | `text`  | `wysiwyg`       |         Oui |           Oui |


| $platform | id      | Type de champ   | Obligatoire | administrable |
| --------- | ------- | --------------- | ----------: | ------------: |
| Titre     | `title` | `wysiwyg light` |         Oui |           Oui |
| Image     | `image` | ``              |         Oui |           Oui |


##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

