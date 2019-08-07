<?php

namespace ColdTrick\GroupTools;

class PageLayout {
	
	/**
	 * Don't allow closed groups to be indexed by search engines
	 *
	 * @param \Elgg\Hook $hook 'head', 'page'
	 *
	 * @return array
	 */
	public static function noIndexClosedGroups(\Elgg\Hook $hook) {
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			// not a group
			return;
		}
		
		if ($page_owner->isPublicMembership()) {
			// public group
			return;
		}
		
		if (elgg_get_plugin_setting('search_index', 'group_tools') === 'yes') {
			// indexing is allowed
			return;
		}
		
		$return = $hook->getValue();
		
		$return['metas']['robots'] = [
			'name' => 'robots',
			'content' => 'noindex',
		];
		
		return $return;
	}
}
