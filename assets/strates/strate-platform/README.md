# Strate Platform



## Options
```php
$options = [
    "menu-name" => "Solutions"
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
    "steps" => [
        "title"=> "Quick Start",
        "items" => [
        [
            "title" => "Log in at <strong>dust.tt</strong> with your agency credentials",
            "image" => 84
        ],
        [
            "title" => "Browse available assistants or create your own",
            "image" => 84
        ],
        [
            "title" => "Connect relevant data sources (Drive, Notion, Slack)",
            "image" => 84
        ],
        [
            "title" => "Start conversing and building workflows",
            "image" => 84
        ]
    ]
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |
| Items     | `items`    | ``              |         Oui |           Oui |
| Etape     | `steps`    | ``              |         Oui |           Oui |


| $items | id      | Type de champ   | Obligatoire | administrable | commentaire |
| ------ | ------- | --------------- | ----------: | ------------: | ----------: |
| Icon   | `icon`  | `select`        |         Oui |           Oui |             |
| Titre  | `title` | `wysiwyg light` |         Oui |           Oui |             |
| Text   | `text`  | `wysiwyg`       |         Oui |           Oui |             |


| $steps | id      | Type de champ             | Obligatoire | administrable | commentaire |
| ------ | ------- | ------------------------- | ----------: | ------------: | ----------: |
| Titre  | `title` | `wysiwyg light`           |         Oui |           Oui |             |
| Etapes | `steps` | `repeteur` - $steps_items |         Oui |           Oui |          id |


| $steps_items | id      | Type de champ   | Obligatoire | administrable | commentaire |
| ------------ | ------- | --------------- | ----------: | ------------: | ----------: |
| Titre        | `title` | `wysiwyg light` |         Oui |           Oui |             |
| Image        | `image` | `image`         |         Oui |           Oui |          id |

##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

