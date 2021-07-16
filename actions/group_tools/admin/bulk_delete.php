<?php

$group_guids = get_input('group_guids');
if (empty($group_guids)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// this could take a while
set_time_limit(0);

/* @var $batch \ElggBatch */
$batch = elgg_get_entities([
	'type' => 'group',
	'guids' => $group_guids,
	'limit' => false,
	'batch' => true,
	'batch_inc_offset' => false,
]);

$failure = false;
/* @var $group \ElggGroup */
foreach ($batch as $group) {
	if (!$group->delete()) {
		$failure = true;
		$batch->reportFailure();
	}
}

if ($failure) {
	return elgg_error_response(elgg_echo('group_tools:action:bulk_delete:error'));
}

return elgg_ok_response('', elgg_echo('group_tools:action:bulk_delete:success'));
