<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\NotificationEventHandler;

class GroupMailEnqueueNotificationEventHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritDoc}
	 */
	public function getSubscriptions(): array {
		$entity = $this->getGroupMail();
		
		// large group could have a lot of recipients, so increase php time limit
		set_time_limit(0);
		
		return $entity->getRecipients();
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getGroupMail();
		
		return $entity->getSubject();
	}
	
	/**
	 * {@inheritDoc}
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
	 * {@inheritDoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
