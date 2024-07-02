<?php

namespace Visuellverstehen\StatamicAdvancedInvalidator\Tests;

use Visuellverstehen\StatamicAdvancedInvalidator\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
