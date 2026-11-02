<?php

namespace VV\AdvancedInvalidator\Tests;

use Mockery;
use Statamic\Facades\GlobalSet;
use Statamic\Globals\Variables;
use Statamic\StaticCaching\Cacher;
use Thoughtco\StatamicCacheTracker\Facades\Tracker;
use VV\AdvancedInvalidator\AdvancedInvalidator;

require_once __DIR__.'/Stubs/CacheTracker.php';

class TagsInvalidationTest extends TestCase
{
    public function test_tags_are_passed_to_cache_tracker(): void
    {
        $rules = ['globals' => ['settings' => ['tags' => ['partial:sitemap']]]];
        config()->set('statamic.static_caching.invalidation.rules', $rules);
        Tracker::$invalidated = [];

        $variables = (new Variables)->globalSet(GlobalSet::make('settings'));

        $cacher = Mockery::mock(Cacher::class)->shouldIgnoreMissing();
        (new AdvancedInvalidator($cacher, $rules))->invalidate($variables);

        $this->assertSame([['partial:sitemap']], Tracker::$invalidated);
    }
}
