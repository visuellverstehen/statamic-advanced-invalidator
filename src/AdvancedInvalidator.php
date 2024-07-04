<?php

namespace VV\AdvancedInvalidator;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav;
use Statamic\Contracts\Taxonomies\Term;
use Statamic\Facades\Site;
use Statamic\StaticCaching\DefaultInvalidator;

class AdvancedInvalidator extends DefaultInvalidator
{
    private $rulePath;

    public function invalidate($item)
    {
        $rules = $this->getItemRules($item);
        
        $this->invalidateKeys($rules);
        $this->invalidateTags($rules);
        
        $this->resolveNamedRoutes($rules);

        parent::invalidate($item);
    }

    protected function invalidateKeys($rules)
    {
        if (! $keys = Arr::get($rules, 'keys')) {
            return;
        }

        $sites = null;

        foreach ($keys as $key) {
            if (strpos($key, '{locale}') === false) {
                Cache::forget($key);

                continue;
            }

            $sites = $sites ?? Site::all();

            foreach ($sites as $site) {
                Cache::forget(str_replace('{locale}', $site->handle(), $key));
            }
        }
    }

    protected function invalidateTags($rules)
    {
        $cacheTracker = '\Thoughtco\StatamicCacheTracker\Facades';
        
        if (! class_exists($cacheTracker)) {
            return;
        }

        if ($tags = Arr::get($rules, 'tags')) {
            $cacheTracker::invalidate($tags);
        }
    }

    private function getItemRules($item): array
    {
        if (empty($rules = config('statamic.static_caching.invalidation.rules'))) {
            return [];
        }

        $rulePath = null;

        switch (true) {
            case $item instanceof Entry:
                $rulePath = 'collections.' . $item->collectionHandle();
                break;
            case $item instanceof Term:
                $rulePath = 'taxonomies.' . $item->taxonomyHandle();
                break;
            case $item instanceof Nav:
                $rulePath = 'navigation.' . $item->handle();
                break;
            case $item instanceof GlobalSet:
                $rulePath = 'globals.' . $item->handle();
                break;
            case $item instanceof Asset:
                $rulePath = 'assets.' . $item->containerHandle();
                break;
            case $item instanceof Form:
                $rulePath = 'forms.' . $item->handle();
                break;
        }

        $this->rulePath = $rulePath;

        return Arr::get($rules, $rulePath, []);
    }

    private function resolveNamedRoutes($rules)
    {
        if (! $routes = Arr::get($rules, 'named_routes')) {
            return;
        }

        $mergedUrls = collect($routes)
            ->map(function ($route) {
                try {
                    $uri = route($route, [], false);
                } catch (Exception) {
                    return;
                }

                return $uri;
            })
            ->filter()
            ->merge(Arr::get($this->rules, $this->rulePath . '.urls'), [])
            ->unique()
            ->toArray();

        Arr::set($this->rules, $this->rulePath . '.urls', $mergedUrls);
        Arr::forget($this->rules, $this->rulePath . '.' . 'named_routes');
    }
}
