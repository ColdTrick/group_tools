<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\NotificationEventHandler;

/**
 * Notification handler for sending out group mails
 */
class GroupMailEnqueueNotificationEventHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	public function getSubscriptions(): array {
		$entity = $this->getGroupMail();
		
		// large group could have a lot of recipients, so increase php time limit
		set_time_limit(0);
		
		return $entity->getRecipients();
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getGroupMail();
		
		return $entity->getSubject();
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getGroupMail();
		
		return $entity->getMessage();
	}
	
	/**
	 * Get the group mail for this notification
	 *
	 * @return \GroupMail
	 */
	protected function getGroupMail(): \GroupMail {
		return $this->event->getObject();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
