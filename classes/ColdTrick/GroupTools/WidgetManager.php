<?php

namespace ColdTrick\GroupTools;

class WidgetManager {
	
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
