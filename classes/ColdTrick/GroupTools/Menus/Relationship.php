<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the relationship menu
 */
class Relationship {

	/**
	 * Change the decline membership request button to a form with motivation for declining
	 *
	 * @param \Elgg\Event $event 'register', 'menu:relationship'
	 *
	 * @return null|MenuItems
	 */
	public static function groupDeclineMembershipReason(\Elgg\Event $event): ?MenuItems {
		$relationship = $event->getParam('relationship');
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'membership_request') {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		if (!$result->has('reject')) {
			return null;
		}
		
		$user = get_entity($relationship->guid_one);
		$group = elgg_call(ELGG_IGNORE_ACCESS, function() use ($relationship) {
			return get_entity($relationship->guid_two);
		});
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if ($page_owner->guid !== $group->guid || !$group->canEdit()) {
			return null;
		}
		
		/* @var $reject \ElggMenuItem */
		$reject = $result->get('reject');
		$reject->setHref(elgg_http_add_url_query_elements('ajax/form/groups/killrequest', [
			'relationship_id' => $relationship->id,
		]));
		$reject->setConfirmText(false);
		$reject->addLinkClass('elgg-lightbox');
		
		return $result;
	}
	
	/**
	 * (un)assign group admins from the group member listing
	 *
	 * @param \Elgg\Event $event 'register', 'menu:relationship'
	 *
	 * @return MenuItems|null
	 */
	public static function toggleGroupAdmin(\Elgg\Event $event): ?MenuItems {
		$user = elgg_get_logged_in_user_entity();
		if (!group_tools_multiple_admin_enabled() || !$user instanceof \ElggUser) {
			return null;
		}
		
		$relationship = $event->getParam('relationship');
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'member') {
			return null;
		}
		
		$member = get_user($relationship->guid_one);
		$group = get_entity($relationship->guid_two);
		if (!$member instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return null;
		}
		
		if (!$group->canEdit($user->guid) || $user->guid === $member->guid || $member->guid === $group->owner_guid) {
			return null;
		}
		
		if (!group_tools_can_assign_group_admin($group)) {
			return null;
		}
		
		/* @var $return_value MenuItems */
		$return_value = $event->getValue();
		
		$is_admin = $member->hasRelationship($group->guid, 'group_admin');
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'group_admin',
			'icon' => 'level-up-alt',
			'text' => elgg_echo('group_tools:multiple_admin:profile_actions:add'),
			'href' => elgg_generate_action_url('group_tools/toggle_admin', [
				'group_guid' => $group->guid,
				'user_guid' => $member->guid,
			]),
			'item_class' => $is_admin ? 'hidden' : '',
			'priority' => 600,
			'data-toggle' => 'group-admin-remove',
		]);
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'group_admin_remove',
			'icon' => 'level-down-alt',
			'text' => elgg_echo('group_tools:multiple_admin:profile_actions:remove'),
			'href' => elgg_generate_action_url('group_tools/toggle_admin', [
				'group_guid' => $group->guid,
				'user_guid' => $member->guid,
			]),
			'item_class' => $is_admin ? '' : 'hidden',
			'priority' => 601,
			'data-toggle' => 'group-admin',
		]);
		
		return $return_value;
	}
}
