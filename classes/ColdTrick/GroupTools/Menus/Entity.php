<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Database\QueryBuilder;
use Elgg\Menu\MenuItems;

/**
 * Add menu items to the entity menu
 */
class Entity {
	
	/**
	 * Remove the group as a related group
	 *
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return null|MenuItems
	 */
	public static function relatedGroup(\Elgg\Event $event): ?MenuItems {
		$page_owner = elgg_get_page_owner_entity();
		$entity = $event->getEntityParam();
		if (!$page_owner instanceof \ElggGroup || !$entity instanceof \ElggGroup) {
			return null;
		}
		
		if ($page_owner->guid === $entity->guid) {
			return null;
		}
		
		if (!$page_owner->canEdit()) {
			return null;
		}
		
		if (!$page_owner->hasRelationship($entity->guid, 'related_group')) {
			return null;
		}
		
		$return_value = $event->getValue();
		
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
	 * Allow toggling the suggested state
	 *
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return null|MenuItems
	 */
	public static function suggestedGroup(\Elgg\Event $event): ?MenuItems {
		if (!elgg_is_admin_logged_in()) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		static $suggested_groups;
		if (!isset($suggested_groups)) {
			$suggested_groups = (string) elgg_get_plugin_setting('suggested_groups', 'group_tools');
			$suggested_groups = elgg_string_to_array($suggested_groups);
		}
		
		$suggested = in_array($entity->guid, $suggested_groups);
		
		/* @var $return_value MenuItems */
		$return_value = $event->getValue();
		
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
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return null|MenuItems
	 */
	public static function addRemoveFromGroup(\Elgg\Event $event): ?MenuItems {
		$group = elgg_get_page_owner_entity();
		if (!$group instanceof \ElggGroup || !$group->canEdit()) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggUser || !$group->isMember($entity)) {
			return null;
		}
		
		if ($entity->guid === elgg_get_logged_in_user_guid() || $group->owner_guid === $entity->guid) {
			// can't remove yourself, or group owner
			return null;
		}
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
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
	 * @param \Elgg\Event $event 'register', 'menu:entity'
	 *
	 * @return null|MenuItems
	 */
	public static function registerApprovalReasons(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('group_profile')) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return null;
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
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
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
}
