<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class GroupsMembers {
	
	/**
	 * Add a menu item to the tabs on the group members page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/member'
	 *
	 * @return void|MenuItems
	 */
	public static function registerGroupAdmins(\Elgg\Hook $hook) {
		
		$entity = $hook->getParam('filter_entity');
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'group_admins',
			'text' => elgg_echo('group_tools:multiple_admin:group_admins'),
			'href' => elgg_generate_url('collection:user:user:group_members', [
				'guid' => $entity->guid,
				'filter' => 'group_admins',
			]),
			'priority' => 250,
		]);
		
		return $result;
	}
	
	/**
	 * Add a menu item to the tabs on the group members page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/member'
	 *
	 * @return void|MenuItems
	 */
	public static function registerEmailInvitations(\Elgg\Hook $hook) {
		
		$entity = $hook->getParam('filter_entity');
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return;
		}
		
		if (elgg_get_plugin_setting('invite_email', 'group_tools') === 'no' && elgg_get_plugin_setting('invite_csv', 'group_tools') === 'no') {
			// no way to get e-mail invitations
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'email_invitations',
			'text' => elgg_echo('group_tools:menu:group_members:email_invitations'),
			'href' => elgg_generate_url('collection:annotation:email_invitation:group', [
				'guid' => $entity->guid,
			]),
			'priority' => 600,
		]);
		
		return $result;
	}
}
