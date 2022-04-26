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
	public static function unregisterRelatedGroupsWidget(\Elgg\Hook $hook) {
		
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
	public static function unregisterGroupActivityWidget(\Elgg\Hook $hook) {
		
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
	
	/**
	 * Set the title URL for the group tools widgets
	 *
	 * @param \Elgg\Hook $hook 'entity:url', 'object'
	 *
	 * @return void|string
	 */
	public static function widgetURL(\Elgg\Hook $hook) {
		
		if (!empty($hook->getValue())) {
			// someone already set an url
			return;
		}
		
		$widget = $hook->getEntityParam();
		if (!$widget instanceof \ElggWidget) {
			return;
		}
		
		switch ($widget->handler) {
			case 'group_members':
				return elgg_generate_url('collection:user:user:group_members', [
					'guid' => $widget->owner_guid,
				]);
				
			case 'group_invitations':
				$user = elgg_get_logged_in_user_entity();
				if (!empty($user)) {
					return elgg_generate_url('collection:group:group:invitations', [
						'username' => $user->username,
					]);
				}
				break;
			case 'group_river_widget':
				if ($widget->context !== 'groups') {
					$group_guid = (int) $widget->group_guid;
				} else {
					$group_guid = $widget->owner_guid;
				}
				
				if (!empty($group_guid)) {
					$group = get_entity($group_guid);
					if ($group instanceof \ElggGroup) {
						return elgg_generate_url('collection:river:group', [
							'guid' => $group->guid,
						]);
					}
				}
				break;
			case 'index_groups':
				return elgg_generate_url('collection:group:group:all');
				
			case 'featured_groups':
				return elgg_generate_url('collection:group:group:all', [
					'filter' => 'featured',
				]);
				
			case 'a_user_groups':
				$owner = $widget->getOwnerEntity();
				if ($owner instanceof \ElggUser) {
					return elgg_generate_url('collection:group:group:member', [
						'username' => $owner->username,
					]);
				}
				break;
			case 'group_related':
				return elgg_generate_url('collection:group:group:related', [
					'guid' => $widget->owner_guid,
				]);
				break;
		}
	}
}
