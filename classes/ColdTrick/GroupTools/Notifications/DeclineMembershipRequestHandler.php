<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification to the user that their membership request for a group was declined
 */
class DeclineMembershipRequestHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:notify:membership:declined:subject', [
			$entity->getDisplayName(),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:notify:membership:declined:subject', [
			$entity->getDisplayName(),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		$reason = $this->getParam('reason');
		if (empty($reason)) {
			return elgg_echo('group_tools:notify:membership:declined:message', [
				$entity->getDisplayName(),
				$entity->getURL(),
			]);
		}
		
		return elgg_echo('group_tools:notify:membership:declined:message:reason', [
			$entity->getDisplayName(),
			$reason,
			$entity->getURL(),
		]);
	}
}
