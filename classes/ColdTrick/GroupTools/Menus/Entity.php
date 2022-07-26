<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Database\QueryBuilder;
use Elgg\Menu\MenuItems;

class Entity {
	
	/**
	 * Remove the group as a related group
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function relatedGroup(\Elgg\Hook $hook) {
		
		$page_owner = elgg_get_page_owner_entity();
		$entity = $hook->getEntityParam();
		if (!$page_owner instanceof \ElggGroup || !$entity instanceof \ElggGroup) {
			return;
		}
		
		if ($page_owner->guid === $entity->guid) {
			return;
		}
		
		if (!$page_owner->canEdit()) {
			return;
		}
		
		if (!$page_owner->hasRelationship($entity->guid, 'related_group')) {
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
	
	/**
	 * Allow toggleing the suggested state
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function suggestedGroup(\Elgg\Hook $hook) {
		
		if (!elgg_is_admin_logged_in()) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		static $suggested_groups;
		if (!isset($suggested_groups)) {
			$suggested_groups = (string) elgg_get_plugin_setting('suggested_groups', 'group_tools');
			$suggested_groups = elgg_string_to_array($suggested_groups);
		}
		
		$suggested = in_array($entity->guid, $suggested_groups);
		
		$return_value = $hook->getValue();
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'set-suggested-group',
			'icon' => 'check',
			'text' => elgg_echo('group_tools:suggested:set'),
			'href' => elgg_generate_action_url('group_tools/admin/toggle_special_state', [
				'group_guid' => $entity->guid,
				'state' => 'suggested',
			]),
			'item_class' => $suggested ? 'hidden' : null,
			'confirm' => true,
			'data-toggle' => 'remove-suggested-group',
			'priority' => 550,
		]);
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'remove-suggested-group',
			'icon' => 'ban',
			'text' => elgg_echo('group_tools:suggested:remove'),
			'href' => elgg_generate_action_url('group_tools/admin/toggle_special_state', [
				'group_guid' => $entity->guid,
				'state' => 'suggested',
			]),
			'confirm' => true,
			'item_class' => !$suggested ? 'hidden' : null,
			'data-toggle' => 'set-suggested-group',
			'priority' => 550,
		]);
		
		return $return_value;
	}
	
	/**
	 * Add the remove user from group menu item to the user entity menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|MenuItems
	 */
	public static function addRemoveFromGroup(\Elgg\Hook $hook) {
		
		$group = elgg_get_page_owner_entity();
		if (!$group instanceof \ElggGroup || !$group->canEdit()) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggUser || !$group->isMember($entity)) {
			return;
		}
		
		if ($entity->guid === elgg_get_logged_in_user_guid() || $group->owner_guid === $entity->guid) {
			// can't remove yourself, or group owner
			return;
		}
		
		/* @var $return MenuItems */
		$return = $hook->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'removeuser',
			'href' => elgg_generate_action_url('groups/remove', [
				'user_guid' => $entity->guid,
				'group_guid' => $group->guid,
			]),
			'text' => elgg_echo('groups:removeuser'),
			'icon' => 'user-times',
			'confirm' => true,
		]);
		
		return $return;
	}
	
	/**
	 * Add a menu item to the approval reasons on the group profile page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 *
	 * @return void|MenuItems
	 */
	public static function registerApprovalReasons(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('group_profile')) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return;
		}
		
		$count = $entity->getAnnotations([
			'count' => true,
			'wheres' => [
				function(QueryBuilder $qb, $main_alias) {
					return $qb->compare("{$main_alias}.name", 'like', 'approval_reason:%', ELGG_VALUE_STRING);
				},
			],
		]);
		if (empty($count)) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'approval_reasons',
			'icon' => 'check-square',
			'text' => elgg_echo('group_tools:group:admin_approve:menu'),
			'href' => elgg_http_add_url_query_elements('ajax/view/group_tools/group/reasons', [
				'guid' => $entity->guid,
			]),
			'link_class' => 'elgg-lightbox',
		]);
		
		return $result;
	}
	
	/**
	 * Add menu items to the user hover/entity menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:user_hover' | 'register', 'menu:entity'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function assignGroupAdmin(\Elgg\Hook $hook) {
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		$loggedin_user = elgg_get_logged_in_user_entity();
		if (!$page_owner instanceof \ElggGroup || empty($loggedin_user)) {
			// not a group or logged in
			return;
		}
		
		if (!$page_owner->canEdit()) {
			// can't edit the group
			return;
		}
		
		$user = $hook->getEntityParam();
		if (!$user instanceof \ElggUser) {
			// not a user menu
			return;
		}
		
		if (($page_owner->owner_guid === $user->guid) || ($loggedin_user->guid === $user->guid)) {
			// group owner or current user
			return;
		}
		
		if (!$page_owner->isMember($user)) {
			// user is not a member of the group
			return;
		}
		
		if (!group_tools_can_assign_group_admin($page_owner)) {
			return;
		}
		
		$return_value = $hook->getValue();
		
		$is_admin = $user->hasRelationship($page_owner->guid, 'group_admin');
		$section = $hook->getType() === 'menu:user_hover' ? 'action' : 'default';
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'group_admin',
			'icon' => 'level-up-alt',
			'text' => elgg_echo('group_tools:multiple_admin:profile_actions:add'),
			'href' => elgg_generate_action_url('group_tools/toggle_admin', [
				'group_guid' => $page_owner->guid,
				'user_guid' => $user->guid,
			]),
			'item_class' => $is_admin ? 'hidden' : '',
			'priority' => 600,
			'section' => $section,
			'data-toggle' => 'group-admin-remove',
		]);
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'group_admin_remove',
			'icon' => 'level-down-alt',
			'text' => elgg_echo('group_tools:multiple_admin:profile_actions:remove'),
			'href' => elgg_generate_action_url('group_tools/toggle_admin', [
				'group_guid' => $page_owner->guid,
				'user_guid' => $user->guid,
			]),
			'item_class' => $is_admin ? '' : 'hidden',
			'priority' => 601,
			'section' => $section,
			'data-toggle' => 'group-admin',
		]);
		
		return $return_value;
	}
}
