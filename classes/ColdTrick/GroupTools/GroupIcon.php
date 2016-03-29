<?php

namespace ColdTrick\GroupTools;

class GroupIcon {
	
	/**
	 * Set the correct url for group thumbnails
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param string $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|string
	 */
	public static function getIconURL($hook, $type, $return_value, $params) {
		
		$group = elgg_extract('entity', $params);
		if (!($group instanceof \ElggGroup)) {
			return;
		}
		
		$size = elgg_extract('size', $params, 'medium');
		$iconsizes = elgg_get_config('icon_sizes');
		if (empty($size) || empty($iconsizes) || !array_key_exists($size, $iconsizes)) {
			return;
		}
		
		$icontime = $group->icontime;
		if (is_null($icontime)) {
			$icontime = 0;
			
			// handle missing metadata (pre 1.7 installations)
			// @see groups_icon_url_override()
			$fh = new \ElggFile();
			$fh->owner_guid = $group->getOwnerGUID();
			$fh->setFilename("groups/{$group->getGUID()}large.jpg");
		
			if ($fh->exists()) {
				$icontime = time();
			}
			
			create_metadata($group->getGUID(), 'icontime', $icontime, 'integer', $group->getOwnerGUID(), ACCESS_PUBLIC);
		}
		
		if (empty($icontime)) {
			return;
		}
		
		$params = [
			'group_guid' => $group->getGUID(),
			'guid' => $group->getOwnerGUID(),
			'size' => $size,
			'icontime' => $icontime,
		];
		return elgg_http_add_url_query_elements('mod/group_tools/pages/groups/thumbnail.php', $params);
	}
	
	/**
	 * Take over the groupicon page handler for fallback
	 *
	 * @param array $page the url elements
	 *
	 * @return void
	 */
	public static function pageHandler($page) {
		
		// group guid
		if (!isset($page[0])) {
			header('HTTP/1.1 400 Bad Request');
			exit;
		}
		$group_guid = (int) $page[0];
		$group = get_entity($group_guid);
		if (!($group instanceof \ElggGroup)) {
			header('HTTP/1.1 400 Bad Request');
			exit;
		}
		
		$icontime = (int) $group->icontime;
		if (empty($icontime)) {
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		$owner_guid = $group->getOwnerGUID();
		
		// size
		$size = 'medium';
		if (isset($page[1])) {
			$icon_sizes = elgg_get_config('icon_sizes');
			if (!empty($icon_sizes) && array_key_exists($page[1], $icon_sizes)) {
				$size = $page[1];
			}
		}
		
		$params = [
			'group_guid' => $group_guid,
			'guid' => $owner_guid,
			'size' => $size,
			'icontime' => $icontime,
		];
		$url = elgg_http_add_url_query_elements('mod/group_tools/pages/groups/thumbnail.php', $params);
		
		// forward to correct (cacheble) url
		forward($url);
	}
}
