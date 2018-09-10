<?php

namespace ColdTrick\GroupTools;

class Views {
	
	/**
	 * Does some view preparation
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'resources/groups/all'
	 *
	 * @return void
	 */
	public static function prepareGroupAll(\Elgg\Hook $hook) {
		$default_filter = elgg_get_plugin_setting('group_listing', 'group_tools');
		if ($default_filter === 'yours' && !elgg_is_logged_in()) {
			$default_filter = 'all';
		}
		$filter = get_input('filter', $default_filter);
		
		// support for 'old' tabs
		switch ($filter) {
			case 'newest':
				set_input('sort', get_input('sort', 'newest'));
				$filter = 'all';
				break;
			case 'popular':
				set_input('sort', get_input('sort', 'popular'));
				$filter = 'all';
				break;
			case 'alpha':
				set_input('sort', get_input('sort', 'alpha'));
				$filter = 'all';
				break;
			default:
				$sorting = elgg_get_plugin_setting("group_listing_{$filter}_sorting", 'group_tools', 'newest');
				set_input('sort', get_input('sort', $sorting));
				break;
		}
		
		set_input('filter', $filter);
	}
	
	/**
	 * Protect group creation page
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'resources/groups/add'
	 *
	 * @return void
	 */
	public static function protectGroupAdd(\Elgg\Hook $hook) {
		if (elgg_get_plugin_setting('limited_groups', 'groups') === 'yes') {
			elgg_admin_gatekeeper();
		}
	}
}
