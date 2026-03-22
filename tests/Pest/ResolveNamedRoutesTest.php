<?php

use Illuminate\Support\Arr;

beforeEach(function () {
    // Set up test routes
    app('router')->get('/test-page', fn () => 'test')->name('test.route');
    app('router')->get('/another-page', fn () => 'another')->name('another.route');
    app('router')->getRoutes()->refreshNameLookups();
});

it('resolves named routes to urls', function () {
    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'named_routes' => ['test.route', 'another.route'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    $reflection = new ReflectionClass($invalidator);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($invalidator);

    // Use Arr::get to check the nested structure
    expect(Arr::get($rules, 'collections.blog.urls'))->toContain('/test-page', '/another-page');
    expect(Arr::get($rules, 'collections.blog.named_routes'))->toBeNull();
});

it('handles missing routes gracefully', function () {
    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'named_routes' => ['test.route', 'nonexistent.route'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    // Should not throw an exception
    $invalidator->invalidate(mockEntry('blog'));

    $reflection = new ReflectionClass($invalidator);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($invalidator);

    // Only valid route should be resolved
    expect(Arr::get($rules, 'collections.blog.urls'))->toContain('/test-page');
    expect(Arr::get($rules, 'collections.blog.urls'))->not->toContain('nonexistent.route');
});

it('merges resolved urls with existing urls', function () {
    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'named_routes' => ['test.route'],
                'urls' => ['/existing-url'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    $reflection = new ReflectionClass($invalidator);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($invalidator);

    expect(Arr::get($rules, 'collections.blog.urls'))->toContain('/test-page', '/existing-url');
});

it('does nothing when no named routes defined', function () {
    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'urls' => ['/blog'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();
    $invalidator->invalidate(mockEntry('blog'));

    $reflection = new ReflectionClass($invalidator);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($invalidator);

    expect(Arr::get($rules, 'collections.blog.urls'))->toBe(['/blog']);
});

it('produces consistent results on multiple invalidate calls', function () {
    config(['statamic.static_caching.invalidation.rules' => [
        'collections' => [
            'blog' => [
                'named_routes' => ['test.route'],
                'urls' => ['/existing-url'],
            ],
        ],
    ]]);

    $invalidator = createInvalidator();

    // First call
    $invalidator->invalidate(mockEntry('blog'));

    $reflection = new ReflectionClass($invalidator);
    $property = $reflection->getProperty('rules');
    $property->setAccessible(true);
    $rules = $property->getValue($invalidator);

    $firstCallUrls = Arr::get($rules, 'collections.blog.urls');

    // Second call - should produce same result
    $invalidator->invalidate(mockEntry('blog'));

    $rules = $property->getValue($invalidator);
    $secondCallUrls = Arr::get($rules, 'collections.blog.urls');

    // Both calls should result in the same URLs
    expect($firstCallUrls)->toBe(['/existing-url', '/test-page']);
    expect($secondCallUrls)->toBe(['/existing-url', '/test-page']);

    // Named routes should be removed after first call
    expect(Arr::get($rules, 'collections.blog.named_routes'))->toBeNull();
});