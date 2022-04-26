<?php
/**
 * jQuery actions to order groups
 */

$guids = get_input('guids');
$order = 1;

if (empty($guids) || !is_array($guids)) {
	return elgg_ok_response();
}

foreach ($guids as $guid) {
	$group = get_entity($guid);
	if (!$group instanceof ElggGroup) {
		continue;
	}
	
	$group->order = $order;
	$order++;
}

return elgg_ok_response();
