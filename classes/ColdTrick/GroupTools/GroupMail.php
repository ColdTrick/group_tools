<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\SubscriptionNotificationEvent;

/**
 * Group Mail notification
 */
class GroupMail {
	
	/**
	 * Tasks todo after the notification has been send
	 *
	 * @param \Elgg\Event $event 'send:after', 'notifications'
	 *
	 * @return void
	 */
	public static function cleanup(\Elgg\Event $event): void {
		$notification_event = $event->getParam('event');
		if (!$notification_event instanceof SubscriptionNotificationEvent) {
			return;
		}
		
		if ($notification_event->getAction() !== 'enqueue') {
			return;
		}
		
		$object = $notification_event->getObject();
		if (!$object instanceof \GroupMail) {
			return;
		}
		
		// remove the mail from the database
		$object->delete();
	}
}
