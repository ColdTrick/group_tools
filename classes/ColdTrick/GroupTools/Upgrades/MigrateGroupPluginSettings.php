<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

class MigrateGroupPluginSettings implements AsynchronousUpgrade {

	/**
	 * {@inheritDoc}
	 */
	public function getVersion(): int {
		return 2021071401;
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function shouldBeSkipped(): bool {
		return empty($this->countItems());
	}

	/**
	 * {@inheritDoc}
	 */
	public function countItems(): int {
		return elgg_count_entities($this->getPrivateSettingOptions()) + elgg_count_entities($this->getMetadataOptions());
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(Result $result, $offset): Result {
		// migrate all groups with private settings
		$limit = 50;
		$groups = elgg_get_entities($this->getPrivateSettingOptions([
			'limit' => $limit,
			'offset' => $offset,
		]));
		
		/* @var $group \ElggGroup */
		foreach ($groups as $group) {
			// default notification settings (no longer used)
			$group->removePrivateSetting('group_tools:default_notifications');
			
			// welcome message
			$welcome = $group->getPrivateSetting('group_tools:welcome_message');
			$new_welcome = $group->getPluginSetting('group_tools', 'welcome_message');
			if (!empty($welcome) && !empty($new_welcome)) {
				$group->setPluginSetting('group_tools', 'welcome_message', $welcome);
			}
			$group->removePrivateSetting('group_tools:welcome_message');
			
			// domain based groups
			$domains = $group->getPrivateSetting('domain_based');
			$new_domains = $group->getPluginSetting('group_tools', 'domain_based');
			if (!empty($domains) && !empty($new_domains)) {
				$domains = explode('|', $domains);
				$domains = array_filter($domains);
				$group->setPluginSetting('group_tools', 'domain_based', implode(',', $domains));
			}
			$group->removePrivateSetting('domain_based');
			
			$result->addSuccesses();
			$limit--;
		}
		
		// if there is room (or later on) migrate metadata settings
		if ($limit > 0) {
			$groups = elgg_get_entities($this->getMetadataOptions([
				'limit' => $limit,
				'offset' => $offset,
			]));
			
			/* @var $group \ElggGroup */
			foreach ($groups as $group) {
				$invite_members = $group->invite_members;
				$new_invite_members = $group->getPluginSetting('group_tools', 'invite_members');
				if (!empty($invite_members) && !empty($new_invite_members)) {
					$group->setPluginSetting('group_tools', 'invite_members', $invite_members);
				}
				unset($group->invite_members);
				
				$result->addSuccesses();
			}
		}
		
		return $result;
	}
	
	/**
	 * Get options for elgg_get_entities()
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getPrivateSettingOptions(array $options = []): array {
		$defaults = [
			'type' => 'group',
			'limit' => 50,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
			'private_setting_names' => [
				'domain_based',
				'group_tools:default_notifications',
				'group_tools:welcome_message',
			],
		];
		
		return array_merge($defaults, $options);
	}
	
	/**
	 * Get options for elgg_get_entities()
	 *
	 * @param array $options additional options
	 *
	 * @return array
	 * @see elgg_get_entities()
	 */
	protected function getMetadataOptions(array $options = []): array {
		$defaults = [
			'type' => 'group',
			'limit' => 50,
			'batch' => true,
			'batch_inc_offset' => $this->needsIncrementOffset(),
			'metadata_names' => [
				'invite_members',
			],
		];
		
		return array_merge($defaults, $options);
	}
}
