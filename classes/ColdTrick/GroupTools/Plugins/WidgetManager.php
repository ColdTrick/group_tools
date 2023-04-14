<?php

namespace ColdTrick\GroupTools\Plugins;

/**
 * Support for the Widget Manager plugin
 */
class WidgetManager {
	
	/**
	 * Add or remove widgets based on the group tool option
	 *
	 * @param \Elgg\Event $event 'group_tool_widgets', 'widget_manager'
	 *
	 * @return null|array
	 */
	public static function groupToolWidgets(\Elgg\Event $event): ?array {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		$return = $event->getValue();
		
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
