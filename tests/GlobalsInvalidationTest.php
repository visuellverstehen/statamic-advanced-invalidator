<?php

namespace VV\AdvancedInvalidator\Tests;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Statamic\Facades\GlobalSet;
use Statamic\Globals\Variables;
use Statamic\StaticCaching\Cacher;
use VV\AdvancedInvalidator\AdvancedInvalidator;

class GlobalsInvalidationTest extends TestCase
{
    public function test_globals_keys_are_forgotten_when_global_variables_are_invalidated(): void
    {
        $rules = ['globals' => ['settings' => ['keys' => ['globals-key']]]];
        config()->set('statamic.static_caching.invalidation.rules', $rules);
        Cache::put('globals-key', 'cached');

        $variables = (new Variables)->globalSet(GlobalSet::make('settings'));

        $cacher = Mockery::mock(Cacher::class)->shouldIgnoreMissing();
        (new AdvancedInvalidator($cacher, $rules))->invalidate($variables);

        $this->assertFalse(Cache::has('globals-key'));
    }
}
