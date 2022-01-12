<?php

namespace ColdTrick\GroupTools;

use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\Clauses\JoinClause;
use Elgg\Database\QueryBuilder;
use Elgg\Exceptions\InvalidArgumentException;

class StaleInfo {

	/**
	 * @var \ElggGroup
	 */
	protected $group;
	
	/**
	 * @var int
	 */
	protected $number_of_days;
	
	/**
	 * Create new Stale info helper
	 *
	 * @param \ElggGroup $entity the group to check
	 * @param int        $days   Number of days before a group becomes stale
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(\ElggGroup $entity, int $days) {
		
		if ($days < 1) {
			throw new InvalidArgumentException('Provide a positive number of days');
		}
		
		$this->group = $entity;
		$this->number_of_days = $days;
	}
	
	/**
	 * Is the group stale
	 *
	 * @return bool
	 */
	public function isStale(): bool {
		
		$compare_ts = time() - ($this->number_of_days * 24 * 60 * 60);
		
		if ($this->group->time_created > $compare_ts) {
			return false;
		}
		
		$ts = $this->getTouchTimestamp();
		if ($ts > $compare_ts) {
			return false;
		}
		
		$ts = $this->getContentTimestamp();
		if ($ts > $compare_ts) {
			return false;
		}
		
		$ts = $this->getCommentTimestamp();
		if ($ts > $compare_ts) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get the timestamp of the last touch/content created in the group
	 *
	 * @return int
	 */
	public function getTimestamp(): int {
		
		$timestamps = [
			$this->group->time_created,
			$this->getTouchTimestamp(),
			$this->getContentTimestamp(),
			$this->getCommentTimestamp(),
		];
		
		return max($timestamps);
	}
	
	/**
	 * Get the timestamp when the group was touched as not stale
	 *
	 * @return int
	 */
	protected function getTouchTimestamp(): int {
		return (int) $this->group->group_tools_stale_touch_ts;
	}
	
	/**
	 * Get the timestamp of the last content in the group
	 *
	 * This is based of searchable entities
	 *
	 * @return int
	 */
	protected function getContentTimestamp(): int {
		
		$object_subtypes = $this::getObjectSubtypes();
		if (empty($object_subtypes)) {
			return 0;
		}
		
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtypes' => $object_subtypes,
			'limit' => 1,
			'container_guid' => $this->group->guid,
			'order_by' => new OrderByClause('time_updated', 'DESC'),
		]);
		if (empty($entities)) {
			return 0;
		}
		
		return (int) $entities[0]->time_updated;
	}
	
	/**
	 * Get the timestamp of the last comment/discussion_reply in the group
	 *
	 * @return int
	 */
	protected function getCommentTimestamp(): int {
		
		$subtypes = ['comment'];
		if (elgg_is_active_plugin('discussions')) {
			$subtypes[] = 'discussion_reply';
		}
		
		$guid = $this->group->guid;
		
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtypes' => $subtypes,
			'limit' => 1,
			'joins' => [
				new JoinClause('entities', 'ce', function(QueryBuilder $qb, $joined_alias, $main_alias) {
					return $qb->compare("$joined_alias.guid", '=', "$main_alias.container_guid");
				}),
			],
			'wheres' => [
				function(QueryBuilder $qb) use ($guid) {
					return $qb->compare('ce.container_guid', '=', $guid, ELGG_VALUE_INTEGER);
				},
			],
			'order_by' => new OrderByClause('time_updated', 'DESC'),
		]);
		if (empty($entities)) {
			return 0;
		}
		
		return (int) $entities[0]->time_updated;
	}
	
	/**
	 * Get supported subtypes for stale info
	 *
	 * @return string[]
	 */
	public static function getObjectSubtypes(): array {
		
		$subtypes = elgg_extract('object', elgg_entity_types_with_capability('searchable'), []);
	
		return elgg_trigger_plugin_hook('stale_info_object_subtypes', 'group_tools', [
			'subtypes' => $subtypes,
		], $subtypes);
	}
}
