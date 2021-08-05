<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\SubscriptionNotificationEvent;

class GroupMail {
	
	/**
	 * Tasks todo after the notification has been send
	 *
	 * @param \Elgg\Hook $hook 'send:after', 'notifications'
	 *
	 * @return void
	 */
	public static function cleanup(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
		if (!$event instanceof SubscriptionNotificationEvent) {
			return;
		}
		
		if ($event->getAction() !== 'enqueue') {
			return;
		}
		
		$object = $event->getObject();
		if (!$object instanceof \GroupMail) {
			return;
		}
		
		// remove the mail from the database
		$object->delete();
	}
}
