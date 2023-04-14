<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the owner_block menu
 */
class OwnerBlock {
	
	/**
	 * Add a link to the related groups page
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return null|MenuItems
	 */
	public static function relatedGroups(\Elgg\Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		if (!$entity->isToolEnabled('related_groups')) {
			return null;
		}
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'related_groups',
			'text' => elgg_echo('group_tools:related_groups:title'),
			'href' => elgg_generate_url('collection:group:group:related', [
				'guid' => $entity->guid,
			]),
			'is_trusted' => true,
		]);
		
		return $return;
	}
}
