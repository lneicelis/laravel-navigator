<?php namespace Luknei\Navigator;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class NavigatorServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->package('luknei/navigator', 'navigator');

		$this->app->bind('navigator', function($app){

            return new NavigatorManager($app);
        });

        $this->app->bindShared('navigator.collector', function($app)
        {
            return new NavigatorItemsCollector();
        });

        $this->app->bindShared('navigator.compiler', function($app)
        {
            $cache = $app['path.storage'].'/views';

            return new BladeCompiler($app['files'], $cache);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('navigator', 'navigator.collector', 'navigator.compiler');
	}

}
