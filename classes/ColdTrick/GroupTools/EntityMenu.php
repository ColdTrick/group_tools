<?php

namespace ColdTrick\GroupTools;

class EntityMenu {
	
	/**
	 * Remove the group as a related group
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function relatedGroup(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('group_tools_related_groups')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		$entity = $hook->getEntityParam();
		if (!$page_owner instanceof \ElggGroup || !$entity instanceof \ElggGroup) {
			return;
		}
		
		if (!$page_owner->canEdit()) {
			return;
		}
		
		$return_value = $hook->getValue();
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'related_group',
			'icon' => 'unlink',
			'text' => elgg_echo('group_tools:related_groups:entity:remove'),
			'href' => elgg_generate_action_url('group_tools/remove_related_groups', [
				'group_guid' => $page_owner->guid,
				'guid' => $entity->guid,
			]),
			'confirm' => true,
		]);
		
		return $return_value;
	}
}
