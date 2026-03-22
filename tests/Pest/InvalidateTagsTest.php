<?php

use Illuminate\Support\Facades\Cache;

it('does nothing when cache tracker class does not exist', function () {
    Cache::put('existing_key', 'value');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'tags' => ['some-tag'],
            ],
        ],
    ]]);

    // CacheTracker class doesn't exist, so tags should not be invalidated
    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    // Cache should be unaffected since CacheTracker doesn't exist
    expect(Cache::get('existing_key'))->toBe('value');
});

it('calls cache tracker invalidate with tags when class exists', function () {
    // Create a mock CacheTracker facade class
    eval('
        namespace Thoughtco\StatamicCacheTracker\Facades;
        class CacheTracker {
            public static $invalidatedTags = [];
            public static function invalidate($tags) {
                self::$invalidatedTags = $tags;
            }
            public static function getInvalidatedTags() {
                return self::$invalidatedTags;
            }
        }
    ');

    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'tags' => ['tag1', 'tag2'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    expect(\Thoughtco\StatamicCacheTracker\Facades\CacheTracker::getInvalidatedTags())->toBe(['tag1', 'tag2']);
});

it('does nothing when no tags defined', function () {
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