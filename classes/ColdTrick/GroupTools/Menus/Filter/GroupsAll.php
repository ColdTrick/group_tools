<?php

namespace ColdTrick\GroupTools\Menus\Filter;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the groups/all filter menu
 */
class GroupsAll {
	
	/**
	 * Rewrite group listing tabs
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:groups/all'
	 *
	 * @return MenuItems
	 */
	public static function addTabs(\Elgg\Event $event): MenuItems {
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'all',
			'text' => elgg_echo('groups:all'),
			'href' => elgg_generate_url('collection:group:group:all', [
				'filter' => 'all',
			]),
			'priority' => 200,
		]);
		
		if (elgg_is_logged_in()) {
			$return[] = \ElggMenuItem::factory([
				'name' => 'yours',
				'text' => elgg_echo('groups:yours'),
				'href' => elgg_generate_url('collection:group:group:all', [
					'filter' => 'yours',
				]),
				'priority' => 250,
			]);
			
			$return[] = \ElggMenuItem::factory([
				'name' => 'member',
				'text' => elgg_echo('group_tools:groups:sorting:member'),
				'href' => elgg_generate_url('collection:group:group:all', [
					'filter' => 'member',
				]),
				'priority' => 260,
			]);
			
			if (group_tools_multiple_admin_enabled()) {
				$return[] = \ElggMenuItem::factory([
					'name' => 'managed',
					'text' => elgg_echo('group_tools:groups:sorting:managed'),
					'href' => elgg_generate_url('collection:group:group:all', [
						'filter' => 'managed',
					]),
					'priority' => 270,
				]);
			}
		}
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'open',
			'text' => elgg_echo('group_tools:groups:sorting:open'),
			'href' => elgg_generate_url('collection:group:group:all', [
				'filter' => 'open',
			]),
			'priority' => 500,
		]);
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'closed',
			'text' => elgg_echo('group_tools:groups:sorting:closed'),
			'href' => elgg_generate_url('collection:group:group:all', [
				'filter' => 'closed',
			]),
			'priority' => 600,
		]);
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'suggested',
			'text' => elgg_echo('group_tools:groups:sorting:suggested'),
			'href' => elgg_generate_url('collection:group:group:all', [
				'filter' => 'suggested',
			]),
			'priority' => 900,
		]);
		
		return $return;
	}
	
	/**
	 * Clean up the tabs on the group listing page
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:groups/all'
	 *
	 * @return MenuItems
	 */
	public static function cleanupTabs(\Elgg\Event $event): MenuItems {
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$new = [];
		
		/* @var $menu_item \ElggMenuItem */
		foreach ($return as $menu_item) {
			$menu_name = $menu_item->getName();
			
			// check plugin settings for the tabs
			if (!self::showTab($menu_name)) {
				continue;
			}
			
			$new[] = $menu_item;
		}
		
		$return->fill($new);
		
		return $return;
	}
	
	/**
	 * Check plugin settings if the tabs should be shown
	 *
	 * @param string $name the (internal) name of the tab
	 *
	 * @return bool
	 */
	protected static function showTab(string $name): bool {
		$show_tab_setting = elgg_get_plugin_setting("group_listing_{$name}_available", 'group_tools');
		
		return ($show_tab_setting !== '0');
	}
}
