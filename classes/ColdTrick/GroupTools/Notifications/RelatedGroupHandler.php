<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notify the group owner of a related group that their group was related to a different group
 */
class RelatedGroupHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		return elgg_echo('group_tools:related_groups:notify:owner:subject');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		return elgg_echo('group_tools:related_groups:notify:owner:subject');
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$actor = $this->getEventActor();
		$entity = $this->getEventEntity();
		$group = $this->getParam('group');
		if (!$actor instanceof \ElggUser || !$entity instanceof \ElggGroup || !$group instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:related_groups:notify:owner:message', [
			$actor->getDisplayName(),
			$entity->getDisplayName(),
			$group->getDisplayName(),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationURL(\ElggUser $recipient, string $method): string {
		$group = $this->getParam('group');
		if (!$group instanceof \ElggGroup) {
			return parent::getNotificationURL($recipient, $method);
		}
		
		return $group->getURL();
	}
}
