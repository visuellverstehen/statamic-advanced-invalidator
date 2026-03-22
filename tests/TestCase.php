<?php

namespace VV\AdvancedInvalidator\Tests;

use VV\AdvancedInvalidator\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
