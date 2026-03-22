<?php

use VV\AdvancedInvalidator\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". By default, we use
| the TestCase from this package which extends Statamic's AddonTestCase.
|
*/

pest()->extend(TestCase::class);

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions.
| The "expect()" function gives you access to a set of "expectations" methods that you can
| use to assert different things. You may extend the Expectation API at any time.
|
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you reduce the number of lines of code in your test files.
|
*/

function createInvalidator(array $rules = []): \VV\AdvancedInvalidator\AdvancedInvalidator
{
    $cacher = app(\Statamic\StaticCaching\Cacher::class);

    // If no rules provided, use the configured invalidation rules
    if (empty($rules)) {
        $rules = config('statamic.static_caching.invalidation.rules', []);
    }

    return new \VV\AdvancedInvalidator\AdvancedInvalidator($cacher, $rules);
}

function mockEntry(string $collectionHandle): \Statamic\Contracts\Entries\Entry
{
    $site = \Mockery::mock(\Statamic\Sites\Site::class);
    $site->shouldReceive('url')->andReturn('/');

    $entry = \Mockery::mock(\Statamic\Contracts\Entries\Entry::class);
    $entry->shouldReceive('collectionHandle')->andReturn($collectionHandle);
    $entry->shouldReceive('descendants')->andReturn(collect([]));
    $entry->shouldReceive('isRedirect')->andReturn(false);
    $entry->shouldReceive('absoluteUrl')->andReturn('http://localhost/test');
    $entry->shouldReceive('site')->andReturn($site);

    return $entry;
}

function mockTerm(string $taxonomyHandle): \Statamic\Contracts\Taxonomies\Term
{
    $term = \Mockery::mock(\Statamic\Contracts\Taxonomies\Term::class);
    $term->shouldReceive('taxonomyHandle')->andReturn($taxonomyHandle);

    return $term;
}

function mockNav(string $handle): \Statamic\Contracts\Structures\Nav
{
    $nav = \Mockery::mock(\Statamic\Contracts\Structures\Nav::class);
    $nav->shouldReceive('handle')->andReturn($handle);
    $nav->shouldReceive('sites')->andReturn(collect(['en']));

    return $nav;
}

function mockGlobalSet(string $handle): \Statamic\Contracts\Globals\GlobalSet
{
    $globalSet = \Mockery::mock(\Statamic\Contracts\Globals\GlobalSet::class);
    $globalSet->shouldReceive('handle')->andReturn($handle);

    return $globalSet;
}

function mockAsset(string $containerHandle): \Statamic\Contracts\Assets\Asset
{
    $container = \Mockery::mock(\Statamic\Contracts\Assets\AssetContainer::class);
    $container->shouldReceive('handle')->andReturn($containerHandle);

    $asset = \Mockery::mock(\Statamic\Contracts\Assets\Asset::class);
    $asset->shouldReceive('containerHandle')->andReturn($containerHandle);
    $asset->shouldReceive('container')->andReturn($container);

    return $asset;
}

function mockForm(string $handle): \Statamic\Contracts\Forms\Form
{
    $form = \Mockery::mock(\Statamic\Contracts\Forms\Form::class);
    $form->shouldReceive('handle')->andReturn($handle);

    return $form;
}