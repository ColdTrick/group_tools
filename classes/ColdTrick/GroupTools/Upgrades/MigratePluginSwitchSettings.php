<?php

namespace ColdTrick\GroupTools\Upgrades;

use Elgg\Upgrade\Result;
use Elgg\Upgrade\SystemUpgrade;

/**
 * Migrates the plugin settings from yes/no to 1/0
 */
class MigratePluginSwitchSettings extends SystemUpgrade {
	
	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2026100601;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function shouldBeSkipped(): bool {
		return false;
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
		return 1;
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function run(Result $result, $offset): Result {
		$plugin = elgg_get_plugin_from_id('group_tools');
		
		$settings = [
			'auto_suggest_groups',
			'multiple_admin',
			'mail',
			'mail_members',
			'related_groups',
			'admin_approve',
			'create_based_on_preset',
			'simple_tool_presets',
			'auto_accept_membership_requests',
			'notification_toggle',
			'invite_email',
			'invite_csv',
			'domain_based',
			'search_index',
		];
		
		foreach ($settings as $setting) {
			$current_setting = $plugin->$setting;
			if (!in_array($current_setting, ['yes', 'no'])) {
				continue;
			}
			
			$plugin->$setting = ($current_setting === 'yes');
		}
		
		$result->addSuccesses();
		$result->markComplete();
		
		return $result;
	}
}
