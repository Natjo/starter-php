# Strate Our foundations



## Options
```php
$options = [
    "menu-name" => "Golden rules"
]
```

## Data Contract
```php
[
    ...$options,
    
    "suptitle" => "✦ Our foundation : the hybrid AI <sup>TM</sup> charter",
    "title" => "Creative value with AI safely, creatively, competively.<br>Six principles that guide how Lonsdale uses AI to outpace creativity, bot replace it.",
    "items" => [
        [
            "title" => "Human authority",
            "text" => "<p>AI augments human intelligence, it never replaces it. We don't create — we curate, direct and validate. Every creative, strategic and final decision is signed off by human experts.</p>",
            "image" => 12
        ],
        [
            "title" => "Brand intelligence system",
            "text" => "<p>Your brand becomes a system, not just aguideline. We build AI systems trained on your brand's codes, not generic datasets — crafted to be distinctive, relevant and lasting.</p>",
            "image" => 12
        ],
        [
            "title" => "Creative super-exploration",
            "text" => "<p>From 10 ideas to 100+ territories explored. We don't generate ideas — we explore entire creative universes. It replaces neither vision, nor art direction, nor judgment.</p>",
            "image" => 12
        ],
        [
            "title" => "Radical transparency",
            "text" => "<p>You always know what is human, what is enhanced, and why. Each deliverable includes a clear AI usage disclosure within a controlled ethical and regulatory framework.</p>",
            "image" => 12
        ],
        [
            "title" => "No-risk AI infrastructure",
            "text" => "<p>No data leakage. No shared learning. No compromise. Aligned with internal governance and global AI standards, your data stays protected at every step.</p>",
            "image" => 12
        ],
        [
            "title" => "Value multiplication",
            "text" => "<p>AI reduces production. Humans multiply impact. This approach lets us go faster and further — enabling scale, speed and amplified impact without compromising standards.</p>",
            "image" => 12
        ]
    ]
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |
| Items     | `items`    | `repeteur`      |         Oui |           Oui |


| $items | id      | Type de champ   | Obligatoire | administrable | commentaire |
| ------ | ------- | --------------- | ----------: | ------------: | ----------: |
| Titre  | `title` | `wysiwyg light` |         Oui |           Oui |             |
| Text   | `text`  | `wysiwyg`       |         Oui |           Oui |             |
| Image  | `image` | `image`         |         Oui |           Oui |          id |



##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

