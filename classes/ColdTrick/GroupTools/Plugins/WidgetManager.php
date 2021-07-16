<?php

namespace ColdTrick\GroupTools\Plugins;

class WidgetManager {
	
	/**
	 * Add or remove widgets based on the group tool option
	 *
	 * @param \Elgg\Hook $hook 'group_tool_widgets', 'widget_manager'
	 *
	 * @return void|array
	 */
	public static function groupToolWidgets(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		$return = $hook->getValue();
		
		// check different group tools for which we supply widgets
		if ($entity->isToolEnabled('related_groups')) {
			$return['enable'][] = 'group_related';
		} else {
			$return['disable'][] = 'group_related';
		}
			
		if ($entity->isToolEnabled('activity')) {
			$return['enable'][] = 'group_river_widget';
		} else {
			$return['disable'][] = 'group_river_widget';
		}
		
		return $return;
	}
}
