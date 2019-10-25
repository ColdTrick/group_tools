<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Database\QueryBuilder;
use Elgg\Upgrade\Result;

class MigrateGroupDefaultContentAccess implements AsynchronousUpgrade {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::getVersion()
	 */
	public function getVersion() {
		return 2019102501;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::needsIncrementOffset()
	 */
	public function needsIncrementOffset() {
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::shouldBeSkipped()
	 */
	public function shouldBeSkipped() {
		return empty($this->countItems());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::countItems()
	 */
	public function countItems() {
		return elgg_count_entities($this->getOptions());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\Upgrade\Batch::run()
	 */
	public function run(Result $result, $offset) {
		
		$groups = elgg_get_entities($this->getOptions());
		if (empty($groups)) {
			$result->markComplete();
			
			return $result;
		}
		
		/* @var $group \ElggGroup */
		foreach ($groups as $group) {
			$content_access = (int) $group->getPrivateSetting('elgg_default_access');
			
			// groups support public, logged in and group members
			if (!in_array($content_access, [ACCESS_PUBLIC, ACCESS_LOGGED_IN])) {
				// private is used for group members
				$content_access = ACCESS_PRIVATE;
			}
			
			$group->content_default_access = $content_access;
			$group->removePrivateSetting('elgg_default_access');
			
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
	protected function getOptions(array $options = []) {
		$defaults = [
			'type' => 'group',
			'limit' => 10,
			'private_setting_name' => 'elgg_default_access',
		];
		
		return array_merge($defaults, $options);
	}
}
