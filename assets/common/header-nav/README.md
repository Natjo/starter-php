# Header nav



## Options


## Data Contract
```php
[
    ...$options,
    
    "nav" => [],
    "link" => [
        "title" => "Acees dust",
        "url" => "#",
        "target" => "_blank",
    ],
]
```

## Acf
| Nom  | id     | Type de champ | Obligatoire | administrable |             remarque |
| ---- | ------ | ------------- | ----------: | ------------: | -------------------: |
| Nav  | `nav`  | -             |         Oui |           Oui | nav qui se construit en fonction de l'option de strate `menu-name`|
| Lien | `link` | `link`        |         Oui |           Oui |            Lien Dust |




##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

