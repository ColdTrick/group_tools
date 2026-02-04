<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEvent;
use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Send a notification when a user is added to a group
 */
class AddUserHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		if (!$group instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:groups:invite:add:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		if (!$group instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:groups:invite:add:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		$actor = ($this->event instanceof InstantNotificationEvent) ? elgg_get_logged_in_user_entity() : $this->getEventActor();
		if (!$group instanceof \ElggGroup || !$actor instanceof \ElggUser) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		$msg = elgg_echo('group_tools:groups:invite:add:body', [
			$actor->getDisplayName(),
			$group->getDisplayName(),
			(string) $this->getParam('add_text'),
			$group->getURL(),
		]);
		
		$params = [
			'group' => $group,
			'inviter' => $actor,
			'invitee' => $recipient,
		];
		return elgg_trigger_event_results('invite_notification', 'group_tools', $params, $msg);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
}
