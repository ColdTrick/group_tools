<?php

/**
 * Group Mail entity
 *
 * @property string $description body of the email
 * @property int[]  $recipients  list of user GUIDs to receive this email
 * @property string $title       subject of the email
 */
class GroupMail extends \ElggObject {
	
	const SUBTYPE = 'group_tools_group_mail';
	
	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes(): void {
		parent::initializeAttributes();
		
		$this->attributes['subtype'] = self::SUBTYPE;
		$this->attributes['access_id'] = ACCESS_PUBLIC;
	}
	
	/**
	 * Get the mail subject
	 *
	 * @return string
	 */
	public function getSubject(): string {
		return $this->title ?? elgg_echo('group_tools:mail:message:default_subject', [$this->getContainerEntity()->getDisplayName()]);
	}
	
	/**
	 * Get the mail message
	 *
	 * @return string
	 */
	public function getMessage(): string {
		/* @var $group \ElggGroup */
		$group = $this->getContainerEntity();
		
		$message = $this->description;
		$message .= PHP_EOL . PHP_EOL;
		$message .= elgg_echo('group_tools:mail:message:from');
		$message .= ": {$group->getDisplayName()}" . PHP_EOL;
		$message .= $group->getURL();
		
		return $message;
	}
	
	/**
	 * Save the recipients for this message
	 *
	 * @param array $recipients GUID array of group members to receive this message
	 *
	 * @return void
	 */
	public function setRecipients(array $recipients): void {
		$this->recipients = $recipients;
	}
	
	/**
	 * Get the recipients for this message in the form [guid => ['email']]
	 *
	 * @return array
	 */
	public function getRecipients(): array {
		if (empty($this->recipients)) {
			return [];
		}
		
		$recipients = (array) $this->recipients;
		/* @var $batch \ElggBatch */
		$batch = elgg_get_entities([
			'type' => 'user',
			'limit' => false,
			'guids' => $recipients,
			'relationship' => 'member',
			'relationship_guid' => $this->container_guid,
			'inverse_relationship' => true,
			'batch' => true,
		]);
		
		$formatted_recipients = [];
		/* @var $user \ElggUser */
		foreach ($batch as $user) {
			$formatted_recipients[$user->guid] = ['email'];
		}
		
		return $formatted_recipients;
	}
	
	/**
	 * Enqueue the mail for delivery
	 *
	 * @return bool
	 */
	public function enqueue(): bool {
		if (!$this->save()) {
			return false;
		}
		
		return elgg_trigger_event('enqueue-mail', 'object', $this);
	}
}
