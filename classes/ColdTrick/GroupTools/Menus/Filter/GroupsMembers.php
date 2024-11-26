<?php

namespace ColdTrick\GroupTools\Menus\Filter;

use Elgg\Database\QueryBuilder;
use Elgg\Menu\MenuItems;

/**
 * Add menu items to the group/members filter menu
 */
class GroupsMembers {
	
	/**
	 * Add a menu item to the tabs on the group members page
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:groups/member'
	 *
	 * @return void|MenuItems
	 */
	public static function registerGroupAdmins(\Elgg\Event $event) {
		$entity = $event->getParam('filter_entity');
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
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
	 * @param \Elgg\Event $event 'register', 'menu:filter:groups/member'
	 *
	 * @return void|MenuItems
	 */
	public static function registerEmailInvitations(\Elgg\Event $event) {
		$entity = $event->getParam('filter_entity');
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return;
		}
		
		if (elgg_get_plugin_setting('invite_email', 'group_tools') === 'no' && elgg_get_plugin_setting('invite_csv', 'group_tools') === 'no') {
			// no way to get e-mail invitations
			return;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'email_invitations',
			'text' => elgg_echo('group_tools:menu:group_members:email_invitations'),
			'href' => elgg_generate_url('collection:annotation:email_invitation:group', [
				'guid' => $entity->guid,
			]),
			'priority' => 600,
			'badge' => elgg_get_annotations([
				'selects' => [
					function(QueryBuilder $qb, $main_alias) {
						return "SUBSTRING_INDEX({$main_alias}.value, '|', -1) AS invited_email";
					},
				],
				'annotation_name' => 'email_invitation',
				'annotation_owner_guid' => $entity->guid,
				'wheres' => [
					function(QueryBuilder $qb, $main_alias) {
						return $qb->compare("{$main_alias}.value", 'LIKE', '%|%', ELGG_VALUE_STRING);
					},
				],
				'count' => true,
			]),
		]);
		
		return $result;
	}
}
