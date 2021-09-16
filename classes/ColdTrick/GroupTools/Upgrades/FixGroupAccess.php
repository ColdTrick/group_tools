<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Database\QueryBuilder;
use Elgg\Upgrade\Result;

class FixGroupAccess implements AsynchronousUpgrade {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::getVersion()
	 */
	public function getVersion(): int {
		return 2019051000;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::needsIncrementOffset()
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::shouldBeSkipped()
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::countItems()
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getOptions());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::run()
	 */
	public function run(Result $result, $offset): Result {
		
		$groups = elgg_get_entities($this->getOptions(['offset' => $offset]));
		if (empty($groups)) {
			$result->markComplete();
			return $result;
		}
		
		/* @var $group \ElggGroup */
		foreach ($groups as $group) {
			$group_acl = $group->getOwnedAccessCollection('group_acl');
			if (!$group_acl instanceof \ElggAccessCollection) {
				$result->addFailures();
				continue;
			}
			
			if (isset($group->intended_access_id) && ((int) $group->intended_access_id === ACCESS_PRIVATE)) {
				$group->intended_access_id = (int) $group_acl->id;
				$result->addSuccesses();
				continue;
			}
			
			$group->access_id = (int) $group_acl->id;
			$group->save();
			
			$result->addSuccesses();
		}
		
		return $result;
	}
	
	/**
	 * Get options for elgg_get_entities
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
			'limit' => 10,
			'wheres' => [
				function (QueryBuilder $qb, $main_alias) {
					$wheres = [];
					
					// no admin approve
					$sub = $qb->subquery('metadata');
					$sub->select('entity_guid')
						->where($qb->compare('name', '=', 'intended_access_id', ELGG_VALUE_STRING));
					
					$wheres[] = $qb->compare("{$main_alias}.guid", 'not in', $sub->getSQL());
					
					// admin approve, with wrong value
					$md = $qb->joinMetadataTable($main_alias, 'guid', null, 'inner', 'aamd');
					
					$admin = [];
					$admin[] = $qb->compare("{$md}.name", '=', 'intended_access_id', ELGG_VALUE_STRING);
					$admin[] = $qb->compare("{$md}.value", '=', ACCESS_PRIVATE, ELGG_VALUE_INTEGER);
					
					$wheres[] = $qb->merge($admin);
					
					return $qb->merge($wheres, 'OR');
				},
			],
		];
		
		return array_merge($defaults, $options);
	}
}
