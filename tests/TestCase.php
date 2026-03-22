<?php

namespace VV\AdvancedInvalidator\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Statamic\Facades\Site;
use VV\AdvancedInvalidator\ServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up multi-site configuration
        config(['statamic.sites.sites' => [
            'en' => ['handle' => 'en', 'url' => '/'],
        ]]);
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            $this->addonServiceProvider,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.watcher', false);
    }
}