<?php

namespace Luknei\Navigator;

use Illuminate\Support\Facades\Facade;

class NavigatorFacade extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'navigator'; }

}
