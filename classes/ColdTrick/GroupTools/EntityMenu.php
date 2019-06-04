<?php

namespace ColdTrick\GroupTools;

use Elgg\Menu\MenuItems;

class EntityMenu {
	
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
		
		if (!check_entity_relationship($page_owner->guid, 'related_group', $entity->guid)) {
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
			$suggested_groups = string_to_tag_array($suggested_groups);
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
}
