<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Send a welcome message to a new group member
 */
class WelcomeMessageGroupHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:welcome_message:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:welcome_message:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		$raw_message = $this->getParam('welcome_message');
		if (!$entity instanceof \ElggGroup || empty($raw_message)) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		// replace the placeholders
		$welcome_message = str_ireplace('[name]', $recipient->getDisplayName(), $raw_message);
		$welcome_message = str_ireplace('[group_name]', $entity->getDisplayName(), $welcome_message);
		$welcome_message = str_ireplace('[group_url]', $entity->getURL(), $welcome_message);
		
		// get notification preferences for this group
		$methods = elgg_get_notification_methods();
		if ($entity->hasSubscription($recipient->guid, $methods)) {
			$subscription = elgg_echo('on');
		} else {
			$subscription = elgg_echo('off');
		}
		
		$subscription = elgg_format_element('b', [], $subscription);
		
		$welcome_message .= PHP_EOL . PHP_EOL . elgg_echo('group_tools:welcome_message:notifications', [
			$subscription,
		]);
		
		return $welcome_message;
	}
}
