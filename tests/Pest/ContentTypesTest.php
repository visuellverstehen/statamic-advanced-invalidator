<?php

use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Set up multi-site configuration
    config(['statamic.sites.sites' => [
        'en' => ['handle' => 'en', 'url' => '/'],
    ]]);
});

it('invalidates cache keys for entry content type', function () {
    Cache::put('entry_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'articles' => [
                'keys' => ['entry_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('articles'));

    expect(Cache::get('entry_key'))->toBeNull();
});

it('invalidates cache keys for term content type', function () {
    Cache::put('term_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'taxonomies' => [
            'tags' => [
                'keys' => ['term_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockTerm('tags'));

    expect(Cache::get('term_key'))->toBeNull();
});

it('invalidates cache keys for nav content type', function () {
    Cache::put('nav_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'navigation' => [
            'main-menu' => [
                'keys' => ['nav_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockNav('main-menu'));

    expect(Cache::get('nav_key'))->toBeNull();
});

it('invalidates cache keys for global set content type', function () {
    Cache::put('global_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'globals' => [
            'site-settings' => [
                'keys' => ['global_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockGlobalSet('site-settings'));

    expect(Cache::get('global_key'))->toBeNull();
});

it('invalidates cache keys for asset content type', function () {
    Cache::put('asset_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'assets' => [
            'images' => [
                'keys' => ['asset_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockAsset('images'));

    expect(Cache::get('asset_key'))->toBeNull();
});

it('invalidates cache keys for form content type', function () {
    Cache::put('form_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'forms' => [
            'contact' => [
                'keys' => ['form_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockForm('contact'));

    expect(Cache::get('form_key'))->toBeNull();
});

it('calls parent invalidate for url invalidation', function () {
    // Setup cacher mock to verify parent::invalidate is called
    $cacher = \Mockery::mock(\Statamic\StaticCaching\Cacher::class);
    $cacher->shouldReceive('invalidateUrls')
        ->once()
        ->with(\Mockery::subset([
            0 => 'http://localhost/test', // from mockEntry()->absoluteUrl()
        ]));

    app()->instance(\Statamic\StaticCaching\Cacher::class, $cacher);

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'urls' => ['/blog'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));
});

it('returns early when item type is not recognized', function () {
    Cache::put('existing_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'keys' => ['existing_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();

    // Pass an unrecognized object
    $unknownItem = new \stdClass();
    $invalidator->invalidate($unknownItem);

    // Cache should not be affected since the item type wasn't recognized
    expect(Cache::get('existing_key'))->toBe('value');
});