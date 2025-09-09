<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification that a new group request was approved by an admin
 */
class ApproveGroupRequestHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:approve:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:approve:summary', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:approve:message', [
			$entity->getDisplayName(),
			$entity->getURL(),
		]);
	}
}
