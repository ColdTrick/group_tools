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
	
	/**
	 * Change some inputs when listing users in livesearch for group invites
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'page/components/list'
	 *
	 * @return void|array
	 */
	public static function livesearchUserListing(\Elgg\Hook $hook) {
		
		$group_guid = (int) get_input('group_guid');
		$group_invite = (bool) get_input('group_invite');
		
		if ($group_guid < 1 || empty($group_invite)) {
			return;
		}
		
		$group = get_entity($group_guid);
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		$vars = $hook->getValue();
		if (elgg_extract('type', $vars) !== 'user') {
			return;
		}
		
		$vars['item_view'] = 'group_tools/group_invite/user';
		$vars['group'] = $group;
		
		return $vars;
	}
}
