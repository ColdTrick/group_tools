<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notify a group owner about their stale group
 */
class StaleGroupHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		
		return elgg_echo('groups_tools:state_info:notification:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('groups_tools:state_info:notification:summary', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('groups_tools:state_info:notification:message', [
			$entity->getDisplayName(),
			$entity->getURL(),
		]);
	}
}
