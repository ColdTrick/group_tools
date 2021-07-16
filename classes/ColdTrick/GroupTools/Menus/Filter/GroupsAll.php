<?php

namespace ColdTrick\GroupTools\Menus\Filter;

class GroupsAll {
	
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
			$return[] = \ElggMenuItem::factory([
				'name' => 'member',
				'text' => elgg_echo('group_tools:groups:sorting:member'),
				'href' => elgg_generate_url('collection:group:group:all', [
					'filter' => 'member',
				]),
				'priority' => 260,
			]);
			$return[] = \ElggMenuItem::factory([
				'name' => 'managed',
				'text' => elgg_echo('group_tools:groups:sorting:managed'),
				'href' => elgg_generate_url('collection:group:group:all', [
					'filter' => 'managed',
				]),
				'priority' => 270,
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
	 * @param \Elgg\Hook $hook 'params', 'menu:filter:groups/all'
	 *
	 * @return array
	 */
	public static function enableSorting(\Elgg\Hook $hook) {
		$result = $hook->getValue();
		
		$allowed_sorting_tabs = [
			'all',
			'yours',
			'open',
			'closed',
			'featured',
			'member',
			'managed',
		];
		$selected_tab = $hook->getParam('filter_value');
		if (!in_array($selected_tab, $allowed_sorting_tabs)) {
			return;
		}
		
		$result['show_sorting'] = true;
		
		return $result;
	}
		
	/**
	 * Add sorting options to the menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/all'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function addSorting(\Elgg\Hook $hook) {
		if (!$hook->getParam('show_sorting', false)) {
			return;
		}
		
		$base_url = current_page_url();
		
		$return = $hook->getValue();
		
		$order = get_input('order');
		if (!in_array($order, ['ASC', 'DESC'])) {
			$order = 'ASC';
		}

		$parent_name = get_input('sort', 'newest');
		if ($parent_name === 'alpha') {
			if ($order === 'ASC') {
				$parent_name = 'alpha_az';
			} else {
				$parent_name = 'alpha_za';
			}
		}
		
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
			'parent_name' => $parent_name,
			'selected' => false,
		]);
		$return[] = \ElggMenuItem::factory([
			'name' => 'alpha_az',
			'icon' => 'sort-alpha-down',
			'text' => elgg_echo('sort:alpha'),
			'title' => elgg_echo('sort:alpha'),
			'href' => elgg_http_add_url_query_elements($base_url, [
				'sort' => 'alpha',
				'order' => 'ASC',
			]),
			'priority' => 200,
			'parent_name' => $parent_name,
			'selected' => false,
		]);
		$return[] = \ElggMenuItem::factory([
			'name' => 'alpha_za',
			'icon' => 'sort-alpha-up',
			'text' => elgg_echo('sort:alpha'),
			'title' => elgg_echo('sort:alpha'),
			'href' => elgg_http_add_url_query_elements($base_url, [
				'sort' => 'alpha',
				'order' => 'DESC',
			]),
			'priority' => 201,
			'parent_name' => $parent_name,
			'selected' => false,
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
			'parent_name' => $parent_name,
			'selected' => false,
		]);
		
		/** @var $parent_item \ElggMenuItem */
		$parent_item = $return->get($parent_name);
		$parent_item->setHref(false);
		$parent_item->setParentName(false);
		$parent_item->setPriority(99999);
		$parent_item->setItemClass('group-tools-listing-sorting');
		$parent_item->setChildMenuOptions([
			'display' => 'dropdown',
			'data-position' => json_encode([
				'my' => 'right top',
				'at' => 'right bottom',
				'collision' => 'fit fit',
			]),
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
		
		$new = [];
		
		/* @var $menu_item \ElggMenuItem */
		foreach ($return as $menu_item) {
			$menu_name = $menu_item->getName();
			
			// check plugin settings for the tabs
			if (!self::showTab($menu_name)) {
				continue;
			}
			
			// check if discussions is enabled
			if (($menu_name === 'discussion') && !elgg_is_active_plugin('discussions')) {
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
		
		$selected_tab = $hook->getParam('filter_value', 'all');
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
