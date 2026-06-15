# Strate Our foundations



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
    
    "suptitle" => "✦ Our foundation : the hybrid AI <sup>TM</sup> charter",
    "title" => "Creative value with AI safely, creatively, competively.<br>Six principles that guide how Lonsdale uses AI to outpace creativity, bot replace it.",
    "items" => [
        [
            "title" => "Human authority",
            "text" => "<p>AI augments human intelligence, it never replaces it. We don't create — we curate, direct and validate. Every creative, strategic and final decision is signed off by human experts.</p>",
            "image" => THEME_UPLOADS . "foundation-1.jpg"
        ],
        [
            "title" => "Brand intelligence system",
            "text" => "<p>Your brand becomes a system, not just aguideline. We build AI systems trained on your brand's codes, not generic datasets — crafted to be distinctive, relevant and lasting.</p>",
            "image" => THEME_UPLOADS . "foundation-2.jpg"
        ],
        [
            "title" => "Creative super-exploration",
            "text" => "<p>From 10 ideas to 100+ territories explored. We don't generate ideas — we explore entire creative universes. It replaces neither vision, nor art direction, nor judgment.</p>",
            "image" => THEME_UPLOADS . "foundation-1.jpg"
        ],
        [
            "title" => "Radical transparency",
            "text" => "<p>You always know what is human, what is enhanced, and why. Each deliverable includes a clear AI usage disclosure within a controlled ethical and regulatory framework.</p>",
            "image" => THEME_UPLOADS . "foundation-2.jpg"
        ],
        [
            "title" => "No-risk AI infrastructure",
            "text" => "<p>No data leakage. No shared learning. No compromise. Aligned with internal governance and global AI standards, your data stays protected at every step.</p>",
            "image" => THEME_UPLOADS . "foundation-1.jpg"
        ],
        [
            "title" => "Value multiplication",
            "text" => "<p>AI reduces production. Humans multiply impact. This approach lets us go faster and further — enabling scale, speed and amplified impact without compromising standards.</p>",
            "image" => THEME_UPLOADS . "foundation-2.jpg"
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


| $items | id      | Type de champ   | Obligatoire | administrable |
| ------ | ------- | --------------- | ----------: | ------------: |
| Titre  | `title` | `wysiwyg light` |         Oui |           Oui |
| Text   | `text`  | `wysiwyg`       |         Oui |           Oui |
| Image  | `image` | ``              |         Oui |           Oui |



##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

