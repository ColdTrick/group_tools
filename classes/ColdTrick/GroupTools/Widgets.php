<?php

namespace ColdTrick\GroupTools;

/**
 * Widget event handler
 */
class Widgets {
	
	/**
	 * Unregister the related groups widget when needed
	 *
	 * @param \Elgg\Event $event 'handlers', 'widgets'
	 *
	 * @return null|\Elgg\WidgetDefinition[]
	 */
	public static function unregisterRelatedGroupsWidget(\Elgg\Event $event): ?array {
		if ($event->getParam('context') !== 'groups') {
			return null;
		}
		
		$remove = false;
		if (elgg_get_plugin_setting('related_groups', 'group_tools') === 'no') {
			// not allowed by plugin setting
			$remove = true;
		}
		
		$container = $event->getParam('container');
		if (!$remove && $container instanceof \ElggGroup) {
			// check if group has tool enabled
			if (!$container->isToolEnabled('related_groups')) {
				$remove = true;
			}
		}
		
		if (!$remove) {
			return null;
		}
		
		/* @var $result \Elgg\WidgetDefinition[] */
		$result = $event->getValue();
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
	 * @param \Elgg\Event $event 'handlers', 'widgets'
	 *
	 * @return null|\Elgg\WidgetDefinition[]
	 */
	public static function unregisterGroupActivityWidget(\Elgg\Event $event): ?array {
		/* @var $result \Elgg\WidgetDefinition[] */
		$result = $event->getValue();
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
	 * @param \Elgg\Event $event 'entity:url', 'object'
	 *
	 * @return null|string
	 */
	public static function widgetURL(\Elgg\Event $event): ?string {
		if (!empty($event->getValue())) {
			// someone already set an url
			return null;
		}
		
		$widget = $event->getEntityParam();
		if (!$widget instanceof \ElggWidget) {
			return null;
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
		}
		
		return null;
	}
}
