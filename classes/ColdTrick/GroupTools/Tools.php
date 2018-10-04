<?php

namespace ColdTrick\GroupTools;

class Tools {
	
	/**
	 * Register the related groups tool option
	 *
	 * @param \Elgg\Hook $hook 'tool_options', 'group'
	 *
	 * @return
	 */
	public static function registerRelatedGroups(\Elgg\Hook $hook) {
		
		if (elgg_get_plugin_setting('related_groups', 'group_tools') === 'no') {
			return;
		}
		
		$tool = new \Elgg\Groups\Tool('related_groups', [
			'label' => elgg_echo('groups_tools:related_groups:tool_option'),
			'default_on' => false,
		]);
		
		/* @var \Elgg\Collections\Collection */
		$result = $hook->getValue();
		
		$result->add($tool);
		
		return $result;
	}
}
