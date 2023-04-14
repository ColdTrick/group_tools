<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\Notification;
use Elgg\Notifications\NotificationEvent;
use Elgg\Database\QueryBuilder;

/**
 * Modifications for group admins
 */
class GroupAdmins {
	
	/**
	 * Add group admins to the notifications about a membership request
	 *
	 * @param \Elgg\Event $event 'get', 'subscriptions'
	 *
	 * @return null|array
	 */
	public static function addGroupAdminsToMembershipRequest(\Elgg\Event $event): ?array {
		$notification_event = $event->getParam('event');
		if (!$notification_event instanceof NotificationEvent) {
			return null;
		}
		
		$action = $notification_event->getAction();
		$entity = $notification_event->getObject();
		if ($action !== 'membership_request' || !$entity instanceof \ElggGroup) {
			return null;
		}
		
		// only send a message if group admins are allowed
		if (!group_tools_multiple_admin_enabled()) {
			return null;
		}
		
		$group_subscriptions = elgg_get_subscriptions_for_container($entity->guid);
		if (empty($group_subscriptions)) {
			return null;
		}
		
		$return = $event->getValue();
		
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
	 * @param \Elgg\Event $event 'prepare', 'notification:membership_request:group:group'
	 *
	 * @return null|Notification
	 */
	public static function prepareMembershipRequestMessage(\Elgg\Event $event): ?Notification {
		$entity = $event->getParam('object');
		$recipient = $event->getParam('recipient'); // group admin (or owner)
		if (!$entity instanceof \ElggGroup || !$recipient instanceof \ElggUser) {
			return null;
		}
		
		if ($entity->owner_guid === $recipient->guid) {
			// owner, message already correct
			return null;
		}
		
		$sender = $event->getParam('sender'); // user requesting membership
		
		/* @var $return Notification */
		$return = $event->getValue();
		
		$return->body = elgg_echo('groups:request:body', [
			$sender->getDisplayName(),
			$entity->getDisplayName(),
			$sender->getURL(),
			$event->getParam('url'),
		], $event->getParam('language'));
		
		return $return;
	}
}
