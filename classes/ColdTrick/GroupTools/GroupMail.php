<?php

namespace ColdTrick\GroupTools;

use Elgg\Notifications\SubscriptionNotificationEvent;

class GroupMail {
	
	/**
	 * Change the notification for a GroupMail
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'notification:enqueue:object:group_tools_group_mail'
	 *
	 * @return void|\Elgg\Notifications\Notification
	 */
	public static function prepareNotification(\Elgg\Hook $hook) {
		
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
		
		$return_value = $hook->getValue();
		
		$return_value->subject = $object->getSubject();
		$return_value->summary = $object->getSubject();
		$return_value->body = $object->getMessage();
		
		return $return_value;
	}
	
	/**
	 * Get the subscribers for the GroupMail
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function getSubscribers(\Elgg\Hook $hook) {
		
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
		
		// large group could have a lot of recipients, so increase php time limit
		set_time_limit(0);
		
		return $object->getRecipients();
	}
	
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
		
		// need to check if the $event actor is a recipient, because Elgg skips that user
		$recipients = $object->getRecipients();
		if (!empty($recipients)) {
			$mail_params = [
				'object' => $object,
				'action' => $event->getAction(),
			];
			
			foreach ($recipients as $user_guid => $methods) {
				if ($user_guid != $event->getActorGUID()) {
					continue;
				}
				
				notify_user($user_guid, $event->getActorGUID(), $object->getSubject(), $object->getMessage(), $mail_params, $methods);
				break;
			}
		}
		
		// remove the mail from the database
		$object->delete();
	}
}
