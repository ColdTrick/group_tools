<?php

namespace ColdTrick\GroupTools\Menus;

class GroupsMembers {
	
	/**
	 * Add a menu item to the tabs on the group members page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:groups_members'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function registerGroupAdmins(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'group_admins',
			'text' => elgg_echo('group_tools:multiple_admin:group_admins'),
			'href' => elgg_generate_url('collection:user:user:group_members', [
				'guid' => $entity->guid,
				'sort' => 'group_admins',
			]),
			'priority' => 600,
		]);
		
		return $result;
	}
}
