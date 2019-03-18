<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\NotificationEvent;

class GroupAdmins {
	
	/**
	 * Cleanup group admin status on group leave
	 *
	 * @param \Elgg\Event $event 'leave', 'group'
	 *
	 * @return void|bool
	 */
	public static function groupLeave(\Elgg\Event $event) {
		
		$params = $event->getObject();
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		// is the user a group admin
		if (!check_entity_relationship($user->guid, 'group_admin', $group->guid)) {
			return;
		}
		
		return remove_entity_relationship($user->guid, 'group_admin', $group->guid);
	}
	
	/**
	 * Add group admins to the notifications about a membership request
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function addGroupAdminsToMembershipRequest(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
		if (!$event instanceof NotificationEvent) {
			return;
		}
		
		$action = $event->getAction();
		$entity = $event->getObject();
		if ($action !== 'membership_request' || !$entity instanceof \ElggGroup) {
			return;
		}
		
		// only send a message if group admins are allowed
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$group_subscriptions = elgg_get_subscriptions_for_container($entity->guid);
		if (empty($group_subscriptions)) {
			return;
		}
		
		$return = $hook->getValue();
		
		// get group admins
		$group_admins = elgg_get_entities([
			'type' => 'user',
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->guid,
			'inverse_relationship' => true,
			'limit' => false,
			'wheres' => [
				"e.guid <> {$entity->owner_guid}",
			],
			'batch' => true,
		]);
		/* @var $group_admin \ElggUser */
		foreach ($group_admins as $group_admin) {
			if (!isset($group_subscriptions[$group_admin->guid])) {
				// group admin has no notification settings for this group
				continue;
			}
			
			if (isset($return[$group_admin->guid])) {
				// already in the subscribers, don't override settings
				continue;
			}
			
			$return[$group_admin->guid] = $group_subscriptions[$group_admin->guid];
		}
		
		return $return;
	}
	
	/**
	 * Prepare the notification message to group admins when a membershiprequest is made
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'notification:membership_request:group:group'
	 *
	 * @return void|\Elgg\Notifications\Notification
	 */
	public static function prepareMembershipRequestMessage(\Elgg\Hook $hook) {
		
		$entity = $hook->getParam('object');
		$recipient = $hook->getParam('recipient'); // group admin (or owner)
		if (!$entity instanceof \ElggGroup || !$recipient instanceof \ElggUser) {
			return;
		}
		
		if ($entity->owner_guid === $recipient->guid) {
			// owner, message already correct
			return;
		}
		
		$language = $hook->getParam('language');
		$sender = $hook->getParam('sender'); // user requesting membership
		
		/* @var $return \Elgg\Notifications\Notification */
		$return = $hook->getValue();
		
		$return->body = elgg_echo('groups:request:body', [
			$recipient->getDisplayName(),
			$sender->getDisplayName(),
			$entity->getDisplayName(),
			$sender->getURL(),
			$hook->getParam('url'),
		], $language);
		
		return $return;
	}
	
	/**
	 * Allow group admins (not owners) to also edit group content
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'group'
	 *
	 * @return void|true
	 */
	public static function permissionsCheck(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already has access
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return;
		}
		
		$entity = $hook->getEntityParam();
		$user = $hook->getParam('user');
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		if (!$entity->isMember($user)) {
			return;
		}
		
		return check_entity_relationship($user->guid, 'group_admin', $entity->guid);
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
		
		$is_admin = check_entity_relationship($user->guid, 'group_admin', $page_owner->guid);
		$section = $hook->getType() === 'menu:user_hover' ? 'action' : 'default';
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'group_admin',
			'icon' => 'level-up',
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
			'icon' => 'level-down',
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
