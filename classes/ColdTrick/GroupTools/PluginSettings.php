<?php

namespace ColdTrick\GroupTools;

/**
 * Plugin settings event handler
 */
class PluginSettings {
	
	/**
	 * Convert an array plugin setting to JSON
	 *
	 * @param \Elgg\Event $event 'plugin_setting', 'group'
	 *
	 * @return null|string
	 */
	public static function saveGroupSettings(\Elgg\Event $event): ?string {
		if ($event->getParam('plugin_id') !== 'group_tools') {
			return null;
		}
		
		$value = $event->getValue();
		if (!is_array($value)) {
			return null;
		}
		
		return json_encode($value);
	}
}
