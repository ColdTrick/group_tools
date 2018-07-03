<?php
/**
 * This class handles the sidebar cleanup for groups
 */

namespace ColdTrick\GroupTools;

class Cleanup {
	
	const SETTING_PREFIX = 'group_tools:cleanup:';
	
	/**
	 * Hide the members listing in the sidebar
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'groups/sidebar/members'
	 *
	 * @return void|array
	 */
	public static function hideSidebarMembers(\Elgg\Hook $hook) {
		
		$vars = $hook->getValue();
		
		$group = elgg_extract('entity', $vars);
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		if ($group->canEdit()) {
			return;
		}
		
		if ($group->getPrivateSetting(self::SETTING_PREFIX . 'members') !== 'yes') {
			return;
		}
		
		$vars[\Elgg\ViewsService::OUTPUT_KEY] = '';
		
		return $vars;
	}
	
	/**
	 * Hide the search box in the sidebar
	 *
	 * @param \Elgg\Hook $hook 'view_vars', 'groups/sidebar/search'
	 *
	 * @return void|array
	 */
	public static function hideSearchbox(\Elgg\Hook $hook) {
		
		$vars = $hook->getValue();
		
		$group = elgg_extract('entity', $vars);
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		if ($group->canEdit()) {
			return;
		}
		
		if ($group->getPrivateSetting(self::SETTING_PREFIX . 'search') !== 'yes') {
			return;
		}
		
		$vars[\Elgg\ViewsService::OUTPUT_KEY] = '';
		
		return $vars;
	}
	
	/**
	 * Hide the extras menu in the sidebar
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'menu:extras'
	 *
	 * @return void|array
	 */
	public static function hideExtrasMenu(\Elgg\Hook $hook) {
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			return;
		}
		
		if ($page_owner->canEdit()) {
			return;
		}
		
		if ($page_owner->getPrivateSetting(self::SETTING_PREFIX . 'extras_menu') !== 'yes') {
			return;
		}
		
		return [];
	}
	
	/**
	 * Hide the membership action buttons
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'menu:title'
	 *
	 * @return void|array
	 */
	public static function hideMembershipActions(\Elgg\Hook $hook) {
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || !elgg_in_context('group_profile')) {
			return;
		}
		
		if ($page_owner->canEdit()) {
			return;
		}
		
		if ($page_owner->getPrivateSetting(self::SETTING_PREFIX . 'actions') !== 'yes') {
			return;
		}
		
		$remove_menu_items = [
			'groups:join',
			'groups:joinrequest',
			'membership_status',
		];
		
		$is_member = $page_owner->isMember();
		
		$return_value = $hook->getValue();
		foreach ($return_value as $section => $menu_items) {
			
			if (!is_array($menu_items)) {
				continue;
			}
			
			/* @var $menu_item \ElggmenuItem */
			foreach ($menu_items as $index => $menu_item) {
				
				if (!in_array($menu_item->getName(), $remove_menu_items)) {
					continue;
				}
				
				if (($menu_item->getName() === 'membership_status') && $is_member) {
					continue;
				}
				
				// remove menu item
				unset($return_value[$section][$index]);
			}
			
			// check if section is empty, to prevent output of empty <ul>
			if (empty($return_value[$section])) {
				unset($return_value[$section]);
			}
		}
		
		return $return_value;
	}
	
	/**
	 * Hide the owner_block menu in the sidebar
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'menu:owner_block'
	 *
	 * @return void|array
	 */
	public static function hideOwnerBlockMenu(\Elgg\Hook $hook) {
		
		$group = $hook->getEntityParam();
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		if ($group->canEdit()) {
			return;
		}
		
		if ($group->getPrivateSetting(self::SETTING_PREFIX . 'menu') !== 'yes') {
			return;
		}
		
		return [];
	}
}
