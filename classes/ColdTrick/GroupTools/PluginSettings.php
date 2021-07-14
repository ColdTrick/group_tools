<?php

namespace ColdTrick\GroupTools;

class PluginSettings {
	
	/**
	 * Convert an array plugin setting to JSON
	 *
	 * @param \Elgg\Hook $hook 'plugin_setting', 'group'
	 *
	 * @return void|string
	 */
	public static function saveGroupSettings(\Elgg\Hook $hook) {
		
		if ($hook->getParam('plugin_id') !== 'group_tools') {
			return;
		}
		
		$value = $hook->getValue();
		if (!is_array($value)) {
			return;
		}
		
		return json_encode($value);
	}
}
