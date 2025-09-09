<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\NotificationEvent;

/**
 * Notification event handler
 */
class Notifications {
	
	/**
	 * Send a confirmation notification to the owner of the group that admin approval is now pending
	 *
	 * @param \Elgg\Event $event 'send:after', 'notifications'
	 *
	 * @return void
	 */
	public static function sendConfirmationOfGroupAdminApprovalToOwner(\Elgg\Event $event): void {
		$notification_event = $event->getParam('event');
		if (!$notification_event instanceof NotificationEvent) {
			return;
		}
		
		$object = $notification_event->getObject();
		if ($notification_event->getAction() !== 'admin_approval' || !$object instanceof \ElggGroup || $object->access_id !== ACCESS_PRIVATE) {
			return;
		}
		
		/* @var $owner \ElggUser */
		$owner = $object->getOwnerEntity();
		
		$owner->notify('admin_approval:owner', $object);
	}
}
