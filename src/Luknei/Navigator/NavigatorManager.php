<?php

namespace Luknei\Navigator;

use Illuminate\Cache\FileStore;
use Illuminate\Foundation\Application;
use Illuminate\View\FileViewFinder;

/**
 * Class NavigatorManager
 * @package Luknei\Navigator
 */
class NavigatorManager
{


    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @var GroupManager
     */
    protected $group;

    /**
     * @var string
     */
    protected $defaultTemplateName = 'navigator::menu';

    /**
     * @param Application $app
     */
    function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * @param string $name
     * @return $this
     */
    public function group($name)
    {
        $name = 'navigator.' . $name;
        try {
            $this->group = $this->app->make($name);
        } catch (\ReflectionException $e) {
            $this->app->bindShared($name, function () {
                return new GroupManager();
            });
            $this->group = $this->app->make($name);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array $variables
     * @return $this
     */
    public function add($name, array $variables)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Name must be a string.');
        }
        $this->group->add($name, $variables);

        return $this;
    }

    /**
     * @param integer $depth
     * @return $this
     */
    public function maxDepth($depth)
    {
        if (!is_integer($depth)) {
            throw new \InvalidArgumentException('Name must be an integer value.');
        }
        $this->group->setMaxDepth($depth);

        return $this;
    }

    /**
     * @param $name
     * @param null $remember
     * @return string
     */
    public function render($name, $remember = null)
    {
        /* If it's cached, then return cahced result*/
        $cached = $this->cacheGet($name);
        if (!is_null($cached)) {
            return $cached;
        }

        $compiledString = $this->compile($name);

        /* Caching the output */
        if (is_int($remember)) {
            $this->cachePut($name, $compiledString, $remember);
        }

        return $compiledString;
    }

    protected function compile($name)
    {
        /** @var FileViewFinder $viewFinder */
        $viewFinder = $this->app['view.finder'];
        $template = file_get_contents($viewFinder->find($name));

        return $this->group->render($template);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function cacheGet($name)
    {
        /** @var FileStore $cache */
        $cache = $this->app['cache.store'];
        $key = $this->cacheKey($name);

        return $cache->get($key);
    }

    /**
     * @param $name
     * @param $string
     * @param $minutes
     */
    protected function cachePut($name, $string, $minutes)
    {
        /** @var FileStore $cache */
        $cache = $this->app['cache.store'];
        $key = $this->cacheKey($name);

        $cache->put($key, $string, $minutes);
    }

    /**
     * @param $key
     * @return string
     */
    protected function cacheKey($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Cache key must be a string method');
        }
        $locale = $this->app->getLocale();

        return '__NAVIGATOR__.' . $locale . '.' . $key;
    }

} 