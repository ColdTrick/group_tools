<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\Result;
use Elgg\Upgrade\SystemUpgrade;

class MigrateConceptAdminApproval extends SystemUpgrade {
	
	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2026020401;
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
		return elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DELETED_ENTITIES, function() {
			return elgg_count_entities($this->getOptions());
		});
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function run(Result $result, $offset): Result {
		return elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DELETED_ENTITIES, function() use ($result, $offset) {
			/** @var \ElggBatch $batch */
			$batch = elgg_get_entities($this->getOptions([
				'offset' => $offset,
			]));
			
			/** @var \ElggGroup $entity */
			foreach ($batch as $entity) {
				if (!$entity->is_concept) {
					$entity->admin_approval = true;
				}
				
				$acl = $entity->getOwnedAccessCollection('group_acl');
				if ($acl instanceof \ElggAccessCollection) {
					$entity->access_id = $acl->id;
				} else {
					$result->addFailures();
					$batch->reportFailure();
					continue;
				}
				
				if ($entity->save()) {
					$result->addSuccesses();
				} else {
					$result->addFailures();
					$batch->reportFailure();
				}
			}
			
			return $result;
		});
	}
	
	/**
	 * Get options to fetch entities which need upgrade
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getOptions(array $options = []): array {
		$defaults = [
			'type' => 'group',
			'access_id' => ACCESS_PRIVATE,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
		];
		
		return array_merge($defaults, $options);
	}
}
