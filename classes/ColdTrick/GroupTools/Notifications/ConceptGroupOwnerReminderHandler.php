<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;
use Elgg\Values;

/**
 * Remind a group owner that their group is still in concept and may be removed soon
 */
class ConceptGroupOwnerReminderHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		$retention_days = (int) $this->getParam('retention');
		if (!empty($retention_days)) {
			return elgg_echo('group_tools:notification:concept_group:expires:subject', [$entity->getDisplayName()]);
		}
		
		return elgg_echo('group_tools:notification:concept_group:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		$retention_days = (int) $this->getParam('retention');
		if (!empty($retention_days)) {
			return elgg_echo('group_tools:notification:concept_group:expires:subject', [$entity->getDisplayName()]);
		}
		
		return elgg_echo('group_tools:notification:concept_group:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		$retention_days = (int) $this->getParam('retention');
		if (!empty($retention_days)) {
			$expires = Values::normalizeTime($entity->time_created);
			$expires->modify("+{$retention_days} days");
			
			
			return elgg_echo('group_tools:notification:concept_group:expires:message', [
				$entity->getDisplayName(),
				elgg_get_friendly_time($expires->getTimestamp()),
				$entity->getURL(),
			]);
		}
		
		return elgg_echo('group_tools:notification:concept_group:message', [
			$entity->getDisplayName(),
			$entity->getURL(),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
}
