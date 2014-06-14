<?php

namespace Luknei\Navigator;

use Config;
use Illuminate\Foundation\Application;

class NavigatorItemsCollector {
    /**
     * @var $providers
     */
    protected $providers;

    /**
     * @var $permissions
     */
    protected $permissions;

    /**
     *
     */
    function __construct()
    {
        $this->providers = $this->getProviders();
    }

    /**
     *
     */
    protected function getProviders()
    {
        if (!is_null($this->providers)) {
            return $this->providers;
        }

        $appProviders = Config::get('app.providers');

        return $this->filterProviders($appProviders);
    }

    /**
     * Filters service providers, since native laravel service privders do not have method authPermissions()
     *
     * @param array $appProviders
     * @return array
     */
    protected function filterProviders(array $appProviders)
    {
        $providers = array();

        foreach ($appProviders as $appProvider) {
            if (!preg_match('/^Illuminate/', $appProvider)) {
                $providers[] = $appProvider;
            }
        }
        return $providers;
    }

    /**
     * Returns an array of all permissions
     *
     */
    public function collect($groupName = '')
    {
        foreach ($this->providers as $provider) {
            $this->callMethod($provider, $groupName);
        }
    }

    /**
     * Collects permissions from single ServiceProvider
     *
     * @param $provider
     * @param $groupName
     */
    protected function callMethod($provider, $groupName)
    {
        $app = new Application;
        $instance = new $provider($app);
        $methodName = $groupName . 'navigator';
        if (method_exists($instance, $methodName)) {
            $instance->{$methodName}();
        }
    }
} 