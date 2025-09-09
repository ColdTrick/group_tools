<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notification about group owner transfer
 */
class GroupOwnerTransferHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		if (!$group instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:notify:transfer:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		if (!$group instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:notify:transfer:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		$actor = $this->getEventActor();
		if (!$group instanceof \ElggGroup || !$actor instanceof \ElggUser) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:notify:transfer:message', [
			$actor->getDisplayName(),
			$group->getDisplayName(),
			$group->getURL(),
		]);
	}
}
