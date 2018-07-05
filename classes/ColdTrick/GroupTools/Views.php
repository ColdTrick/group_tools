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
		group_tools_prepare_listing_settings();
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
