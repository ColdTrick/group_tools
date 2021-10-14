<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\NotificationEvent;
use Elgg\Database\QueryBuilder;

class GroupAdmins {
	
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
				function (QueryBuilder $qb, $main_alias) use ($entity) {
					return $qb->compare("{$main_alias}.guid", '!=', $entity->owner_guid, ELGG_VALUE_GUID);
				},
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
			$sender->getDisplayName(),
			$entity->getDisplayName(),
			$sender->getURL(),
			$hook->getParam('url'),
		], $language);
		
		return $return;
	}
}
