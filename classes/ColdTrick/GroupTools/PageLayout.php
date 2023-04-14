<?php

namespace ColdTrick\GroupTools;

/**
 * Page event handler
 */
class PageLayout {
	
	/**
	 * Don't allow closed groups to be indexed by search engines
	 *
	 * @param \Elgg\Event $event 'head', 'page'
	 *
	 * @return null|array
	 */
	public static function noIndexClosedGroups(\Elgg\Event $event): ?array {
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			// not a group
			return null;
		}
		
		if ($page_owner->isPublicMembership()) {
			// public group
			return null;
		}
		
		if (elgg_get_plugin_setting('search_index', 'group_tools') === 'yes') {
			// indexing is allowed
			return null;
		}
		
		$return = $event->getValue();
		
		$return['metas']['robots'] = [
			'name' => 'robots',
			'content' => 'noindex',
		];
		
		return $return;
	}
}
