<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\Result;
use Elgg\Upgrade\SystemUpgrade;

/**
 * Migrate from a group tool setting to a group plugin setting
 */
class MigrateAssignGroupAdmins extends SystemUpgrade {
	
	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2026081101;
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
		/** @var \ElggBatch $groups */
		$groups = elgg_get_entities($this->getOptions([
			'offset' => $offset,
		]));
		/** @var \ElggGroup $group */
		foreach ($groups as $group) {
			if ($group->group_multiple_admin_allow_enable === 'yes') {
				$r = $group->setPluginSetting('group_tools', 'assign_group_admins', true);
			} else {
				$r = $group->setPluginSetting('group_tools', 'assign_group_admins', false);
			}
			
			if ($r) {
				$result->addSuccesses();
				unset($group->group_multiple_admin_allow_enable);
			} else {
				$result->addFailures();
				$groups->reportFailure();
			}
		}
		
		return $result;
	}
	
	/**
	 * Get selection options for the migration
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getOptions(array $options = []): array {
		$defaults = [
			'type' => 'group',
			'metadata_name' => 'group_multiple_admin_allow_enable',
			'limit' => 100,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
		];
		
		return array_merge($defaults, $options);
	}
}
