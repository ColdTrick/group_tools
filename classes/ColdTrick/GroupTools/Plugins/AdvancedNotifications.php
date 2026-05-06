<?php

namespace ColdTrick\GroupTools\Plugins;

use Elgg\Notifications\NotificationEvent;

/**
 * Changes for advanced notifications
 */
class AdvancedNotifications {
	
	/**
	 * Don't validate de ACL membership for admin_approval notifications
	 *
	 * @param \Elgg\Event $event 'validate:acl_membership', 'advanced_notifications'
	 *
	 * @return bool|null
	 */
	public static function allowAdminApprovalNotifications(\Elgg\Event $event): ?bool {
		$notification = $event->getParam('event');
		if (!$notification instanceof NotificationEvent) {
			return null;
		}
		
		$action = $notification->getAction();
		$group = $notification->getObject();
		if ($action !== 'admin_approval' || !$group instanceof \ElggGroup) {
			return null;
		}
		
		return false;
	}
}
