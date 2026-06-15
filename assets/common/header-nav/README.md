# Header nav



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
    
    "nav" => "",
    "link" => [
        "title" => "Acees dust",
        "url" => "#",
        "target" => "_blank",
    ],
]
```

## Acf
| Nom  | id            | Type de champ | Obligatoire | administrable |       remarque |
| ---- | ------------- | ------------- | ----------: | ------------: | -------------: |
| Nav  | `menu-header` | ``            |         Oui |           Oui | Menu wordpress |
| Lien | `link`        | ``            |         Oui |           Oui |      Lien Dust |




##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`

