<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification that a new group request was declined by an admin
 */
class DeclineGroupRequestHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:decline:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:decline:summary', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:decline:message', [
			$entity->getDisplayName(),
			(string) $this->getParam('reason'),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationURL(\ElggUser $recipient, string $method): string {
		return '';
	}
}
