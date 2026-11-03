<?php

namespace Thoughtco\StatamicCacheTracker\Facades;

// Stands in for the optional thoughtco/statamic-cache-tracker facade.
class Tracker
{
    public static array $invalidated = [];

    public static function invalidate(array $tags = []): void
    {
        static::$invalidated[] = $tags;
    }
}
