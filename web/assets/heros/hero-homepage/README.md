# Hero Homepage



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

    "suptitle" => "Internal AI Hub",
    "title" => "AI at<br>the service<br>of creativity",
    "text" => "<p>Richard is your internal hub for AI-powered creative excellence.
Discover tolls, best practices, and resources to elevate every project.</p>",
    
]
```

## Acf
| Nom       | id         | Type de champ   | Obligatoire | administrable |
| --------- | ---------- | --------------- | ----------: | ------------: |
| Sur titre | `suptitle` | `wysiwyg light` |         Oui |           Oui |
| Titre     | `title`    | `wysiwyg light` |         Oui |           Oui |
| Text      | `text`     | `wysiwyg`       |         Oui |           Oui |



##  Rules


## Dependencies
- `component::eyebrow()`
- `component::title()`
- `component::text()`
- `component::picture()`

