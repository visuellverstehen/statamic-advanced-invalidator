<?php

namespace VV\AdvancedInvalidator\Tests;

use Statamic\Testing\AddonTestCase;
use VV\AdvancedInvalidator\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
