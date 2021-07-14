<?php

namespace ColdTrick\GroupTools;

class Widgets {
	
	/**
	 * Unregister the related groups widget when needed
	 *
	 * @param \Elgg\Hook $hook 'handlers', 'widgets'
	 *
	 * @return void|\Elgg\WidgetDefinition[]
	 */
	public static function unregsiterRelatedGroupsWidget(\Elgg\Hook $hook) {
		
		if ($hook->getParam('context') !== 'groups') {
			return;
		}
		
		$remove = false;
		if (elgg_get_plugin_setting('related_groups', 'group_tools') === 'no') {
			// not allowed by plugin setting
			$remove = true;
		}
		
		$container = $hook->getParam('container');
		if (!$remove && $container instanceof \ElggGroup) {
			// check if group has tool enabled
			if (!$container->isToolEnabled('related_groups')) {
				$remove = true;
			}
		}
		
		if (!$remove) {
			return;
		}
		
		/* @var $result \Elgg\WidgetDefinition[] */
		$result = $hook->getValue();
		foreach ($result as $index => $definition) {
			if ($definition->id !== 'group_related') {
				continue;
			}
			
			unset($result[$index]);
			break;
		}
		
		return $result;
	}
	
	/**
	 * Unregister the groups activity widget because our version is better
	 *
	 * @param \Elgg\Hook $hook 'handlers', 'widgets'
	 *
	 * @return void|\Elgg\WidgetDefinition[]
	 */
	public static function unregsiterGroupActivityWidget(\Elgg\Hook $hook) {
		
		/* @var $result \Elgg\WidgetDefinition[] */
		$result = $hook->getValue();
		foreach ($result as $index => $definition) {
			if ($definition->id !== 'group_activity') {
				continue;
			}
			
			unset($result[$index]);
			break;
		}
		
		return $result;
	}
}
