<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add popular group sorting to a filter menu
 *
 * @since 4.2
 */
class FilterSortItems {
	
	/**
	 * Register sorting menu items based on the relationship 'member'
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter<:some filter_id>'
	 *
	 * @return null|MenuItems
	 */
	public static function registerPopularSorting(\Elgg\Event $event): ?MenuItems {
		if (!(bool) $event->getParam('filter_sorting', true)) {
			// sorting is disabled for this menu
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		/* @var $menu_item \ElggMenuItem */
		foreach ($result as $menu_item) {
			if ($menu_item->getParentName() !== 'sort:parent') {
				continue;
			}
			
			$menu_item->setHref(elgg_http_remove_url_query_element($menu_item->getHref(), 'sort'));
		}
		
		// add sorting based on relationship time_created
		$result[] = \ElggMenuItem::factory([
			'name' => 'popular',
			'icon' => 'sort-numeric-down-alt',
			'text' => elgg_echo('sort:popular'),
			'href' => elgg_http_add_url_query_elements(elgg_get_current_url(), [
				'sort' => 'popular',
				'sort_by' => null, // unset core filter options
			]),
			'parent_name' => 'sort:parent',
			'priority' => 200,
		]);

		return $result;
	}
}
