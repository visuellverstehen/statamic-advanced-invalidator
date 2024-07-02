# Statamic Advanced Invalidator

A Statamic invalidation class for static caching with advanced capabilities. It allows invalidating named routes, cache keys and [CacheTracker](https://github.com/thoughtco/statamic-cache-tracker) tags when the static cache for content items is cleared.

## How to Install

Simply run the following command from your project root:

``` bash
composer require visuellverstehen/statamic-advanced-invalidator
```

## How to Use

In your `static_caching.php` file add AdvancedInvalidator for the `class` key in the `invalidation` section. That allows you to add additional invalidation rules:

```php
use VV\AdvancedInvalidator\AdvancedInvalidator;

// …

'invalidation' => [
    'class' => AdvancedInvalidator::class,
    
    'rules' => [
        'collections' => [
            'pages' => [
                'urls' => [
                    // urls to invalidate
                ],
                'named_routes' => [
                    // route names
                    // routes will be resolved and merged with urls above
                    'user.login',
                ],
                'keys' => [
                    // cache key names
                    'my-cache-key',
                ],
                'tags' => [
                    // when using the CacheTracker package you can add
                    // tags that should be invalidated
                    'partial:sitemap',
                ],
            ],
        ],
    ]
]
```

## More about us

- [www.visuellverstehen.de](https://visuellverstehen.de)

## License
The MIT license (MIT). Please take a look at the [license file](LICENSE.md) for more information.

