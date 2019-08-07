<?php

namespace ColdTrick\GroupTools;

class OwnerBlockMenu {
	
	/**
	 * Add a link to the related groups page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:owner_block'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function relatedGroups(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		if (!$entity->isToolEnabled('related_groups')) {
			return;
		}
		
		$return = $hook->getValue();
		
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
