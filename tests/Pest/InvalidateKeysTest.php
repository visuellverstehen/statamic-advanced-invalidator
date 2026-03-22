<?php

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Site;

beforeEach(function () {
    // Set up multi-site configuration for locale tests
    config(['statamic.sites.sites' => [
        'en' => ['handle' => 'en', 'url' => '/en'],
        'de' => ['handle' => 'de', 'url' => '/de'],
    ]]);
});

it('forgets simple cache keys', function () {
    Cache::put('simple_cache_key', 'value');
    expect(Cache::get('simple_cache_key'))->toBe('value');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'keys' => ['simple_cache_key'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(Cache::get('simple_cache_key'))->toBeNull();
});

it('forgets locale-specific cache keys', function () {
    Cache::put('cache_key_en', 'value_en');
    Cache::put('cache_key_de', 'value_de');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'keys' => ['cache_key_{locale}'],
            ],
        ],
    ]]);

    // Mock Site::all() to return site objects with handles
    $siteEn = \Mockery::mock(\Statamic\Sites\Site::class);
    $siteEn->shouldReceive('handle')->andReturn('en');

    $siteDe = \Mockery::mock(\Statamic\Sites\Site::class);
    $siteDe->shouldReceive('handle')->andReturn('de');

    Site::shouldReceive('all')
        ->once()
        ->andReturn(collect([$siteEn, $siteDe]));

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(Cache::get('cache_key_en'))->toBeNull();
    expect(Cache::get('cache_key_de'))->toBeNull();
});

it('handles mixed keys', function () {
    Cache::put('static_key', 'value_static');
    Cache::put('locale_key_en', 'value_en');
    Cache::put('locale_key_de', 'value_de');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'keys' => ['static_key', 'locale_key_{locale}'],
            ],
        ],
    ]]);

    // Mock Site::all() to return site objects with handles
    $siteEn = \Mockery::mock(\Statamic\Sites\Site::class);
    $siteEn->shouldReceive('handle')->andReturn('en');

    $siteDe = \Mockery::mock(\Statamic\Sites\Site::class);
    $siteDe->shouldReceive('handle')->andReturn('de');

    Site::shouldReceive('all')
        ->once()
        ->andReturn(collect([$siteEn, $siteDe]));

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(Cache::get('static_key'))->toBeNull();
    expect(Cache::get('locale_key_en'))->toBeNull();
    expect(Cache::get('locale_key_de'))->toBeNull();
});

it('does nothing when no keys defined', function () {
    Cache::put('existing_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'urls' => ['/blog'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(Cache::get('existing_key'))->toBe('value');
});

it('does nothing when rules empty', function () {
    Cache::put('existing_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => []]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(Cache::get('existing_key'))->toBe('value');
});