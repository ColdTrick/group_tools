<?php

namespace ColdTrick\GroupTools;

class Router {
		
	/**
	 * Allow registration with a valid group invite code
	 *
	 * Both access to the registration page and the registration action
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param null   $params       supplied params
	 *
	 * @return void
	 */
	public static function allowRegistration($hook, $type, $return_value, $params) {
		
		// enable registration if disabled
		group_tools_enable_registration();
	}
}
