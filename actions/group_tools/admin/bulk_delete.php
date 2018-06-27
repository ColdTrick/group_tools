<?php

$group_guids = get_input('group_guids');
if (empty($group_guids)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// this could take a while
set_time_limit(0);

$options = [
	'type' => 'group',
	'guids' => $group_guids,
	'limit' => false,
];

$batch = new ElggBatch('elgg_get_entities', $options, 'elgg_batch_delete_callback', 25, false);
if (!$batch->callbackResult) {
	return elgg_error_response(elgg_echo('group_tools:action:bulk_delete:error'));
}

return elgg_ok_response('', elgg_echo('group_tools:action:bulk_delete:success'));
