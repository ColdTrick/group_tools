<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\Result;
use Elgg\Upgrade\SystemUpgrade;

/**
 * Migrate a plugin user setting to a user notification setting
 */
class MigrateNotificationSettings extends SystemUpgrade {
	
	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2024061101;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getOptions());
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function run(Result $result, $offset): Result {
		$methods = elgg_get_notification_methods();
		
		/* @var $users \ElggBatch */
		$users = elgg_get_entities($this->getOptions([
			'offset' => $offset,
		]));
		/* @var $user \ElggUser */
		foreach ($users as $user) {
			$notify = (bool) $user->getPluginSetting('group_tools', 'notify_approval');
			
			foreach ($methods as $method) {
				$user->setNotificationSetting($method, $notify, 'group_tools_group_approval');
			}
			
			if ($user->removePluginSetting('group_tools', 'notify_approval')) {
				$result->addSuccesses();
			} else {
				$result->addFailures();
			}
		}
		
		return $result;
	}
	
	/**
	 * Options for fetching admins
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getOptions(array $options = []): array {
		$defaults = [
			'type' => 'user',
			'metadata_name_value_pairs' => [
				'name' => 'admin',
				'value' => 'yes',
			],
			'metadata_name' => 'plugin:user_setting:group_tools:notify_approval',
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
		];
		
		return array_merge($defaults, $options);
	}
}
