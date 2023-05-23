<?php

namespace ColdTrick\GroupTools\Plugins;

use ColdTrick\ProfileManager\FieldType;

/**
 * Modifications for the Profile Manager plugin
 */
class ProfileManager {
	
	/**
	 * Register a group profile field type
	 *
	 * @param \Elgg\Event $event 'types:custom_group_field', 'profile_manager'
	 *
	 * @return array
	 */
	public static function registerGroupFields(\Elgg\Event $event): array {
		$result = $event->getValue();
		
		$result[] = FieldType::factory([
			'type' => 'group_tools_preset',
			'name' => elgg_echo('group_tools:profile:field:group_tools_preset'),
			'options' => [
				'user_editable' => true,
				'output_as_tags' => true,
				'admin_only' => true,
			],
		]);
		
		return $result;
	}
}
