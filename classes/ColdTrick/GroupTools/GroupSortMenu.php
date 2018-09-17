<?php

namespace ColdTrick\GroupTools;

class GroupSortMenu {
	
	/**
	 * Rewrite group listing tabs
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/all'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function removeTabs(\Elgg\Hook $hook) {
		
		/* @var $return \Elgg\Menu\MenuItems */
		$return = $hook->getValue();
		
		$remove_items = [
			'newest',
			'popular',
			'alpha',
		];
		foreach ($remove_items as $name) {
			$return->remove($name);
		}
		
		return $return;
	}
	
	/**
	 * Rewrite group listing tabs
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/all'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function addTabs(\Elgg\Hook $hook) {
		
		/* @var $return \Elgg\Menu\MenuItems */
		$return = $hook->getValue();
		
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
	 * Add sorting options to the menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/all'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function addSorting(\Elgg\Hook $hook) {
		
		$allowed_sorting_tabs = [
			'all',
			'yours',
			'open',
			'closed',
			'featured',
		];
		$selected_tab = $hook->getParam('filter_value');
		if (!in_array($selected_tab, $allowed_sorting_tabs)) {
			return;
		}
		
		$base_url = current_page_url();
		
		$return = $hook->getValue();
		
		$order = get_input('order');
		if (!in_array($order, ['ASC', 'DESC'])) {
			$order = 'ASC';
		}
		
		// main sorting menu item
		$return[] = \ElggMenuItem::factory([
			'name' => 'sorting',
			'icon' => 'sort',
			'text' => false,
			'title' => elgg_echo('sort'),
			'href' => false,
			'priority' => 99999, // needs to be last
			'child_menu' => [
				'display' => 'dropdown',
				'data-position' => json_encode([
					'my' => 'right top',
					'at' => 'right bottom',
					'collision' => 'fit fit',
				]),
			],
		]);
		
		// add sorting options
		$return[] = \ElggMenuItem::factory([
			'name' => 'newest',
			'icon' => 'sort-amount-desc',
			'text' => elgg_echo('sort:newest'),
			'title' => elgg_echo('sort:newest'),
			'href' => elgg_http_add_url_query_elements($base_url, [
				'sort' => 'newest',
			]),
			'priority' => 100,
			'parent_name' => 'sorting',
			'selected' => get_input('sort') === 'newest',
		]);
		$return[] = \ElggMenuItem::factory([
			'name' => 'alpha',
			'icon' => $order === 'ASC' ? 'sort-alpha-up' : 'sort-alpha-down',
			'text' => elgg_echo('sort:alpha'),
			'title' => elgg_echo('sort:alpha'),
			'href' => elgg_http_add_url_query_elements($base_url, [
				'sort' => 'alpha',
				'order' => $order === 'ASC' ? 'DESC': 'ASC',
			]),
			'priority' => 200,
			'parent_name' => 'sorting',
			'selected' => get_input('sort') === 'alpha',
		]);
		$return[] = \ElggMenuItem::factory([
			'name' => 'popular',
			'icon' => 'sort-numeric-desc',
			'text' => elgg_echo('sort:popular'),
			'title' => elgg_echo('sort:popular'),
			'href' => elgg_http_add_url_query_elements($base_url, [
				'sort' => 'popular',
			]),
			'priority' => 300,
			'parent_name' => 'sorting',
			'selected' => get_input('sort') === 'popular',
		]);
		
		return $return;
	}
	
	/**
	 * Clean up the tabs on the group listing page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/all'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function cleanupTabs(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		
		/* @var $menu_item \ElggMenuItem */
		foreach ($return as $index => $menu_item) {
			$menu_name = $menu_item->getName();
			
			// check plugin settings for the tabs
			if (!self::showTab($menu_name)) {
				unset($return[$index]);
				continue;
			}
			
			// check if discussions is enabled
			if (($menu_name === 'discussion') && !elgg_is_active_plugin('discussions')) {
				unset($return[$index]);
				continue;
			}
		}
		
		return $return;
	}
	
	/**
	 * Check plugin settings if the tabs should be shown
	 *
	 * @param string $name the (internal) name of the tab
	 *
	 * @return bool
	 */
	protected static function showTab($name) {
		
		$show_tab_setting = elgg_get_plugin_setting("group_listing_{$name}_available", 'group_tools');
		
		return ($show_tab_setting !== '0');
	}
	
	/**
	 * Set the correct selected tab
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'menu:filter:groups/all'
	 *
	 * @return void|\Elgg\Menu\PreparedMenu
	 */
	public static function setSelected(\Elgg\Hook $hook) {
		
		$selected_tab = $hook->getParam('selected', 'all');
		if (empty($selected_tab)) {
			return;
		}
		
		/* @var $return \Elgg\Menu\PreparedMenu */
		$return = $hook->getValue();
		
		foreach ($return as $section) {
			/* @var $menu_item \ElggMenuItem */
			foreach ($section as $menu_item) {
				if ($menu_item->getName() !== $selected_tab) {
					continue;
				}
				
				$menu_item->setSelected(true);
				break(2);
			}
		}
		
		return $return;
	}
}
