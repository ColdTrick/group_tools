<?php

namespace ColdTrick\GroupTools;

class Router {
	
	/**
	 * Take over the groups page handler in some cases
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param null   $params       supplied params
	 *
	 * @return void|false
	 */
	public static function groups($hook, $type, $return_value, $params) {
		
		if (empty($return_value) || !is_array($return_value)) {
			return;
		}
		
		$resource_loaded = false;
		
		$page = elgg_extract('segments', $return_value);
		switch (elgg_extract(0, $page, 'all')) {
			case 'all':
				// prepare tab listing settings
				group_tools_prepare_listing_settings();
				break;
			case 'add':
				if (elgg_get_plugin_setting('limited_groups', 'groups') === 'yes') {
					elgg_admin_gatekeeper();
				}
				break;
		}
		
		// did we want this page?
		if ($resource_loaded) {
			// done by resource view
			return false;
		}
	}
	
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
