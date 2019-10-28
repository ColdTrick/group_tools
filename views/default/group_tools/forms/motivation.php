<?php

use Elgg\EntityNotFoundException;

$group_guid = (int) get_input('guid');
$group = elgg_call(ELGG_IGNORE_ACCESS, function () use ($group_guid) {
	return get_entity($group_guid);
});

if (!$group instanceof ElggGroup) {
	throw new EntityNotFoundException();
}

$title = elgg_echo('group_tools:join_motivation:title', [$group->getDisplayName()]);

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:join_motivation:description', [$group->getDisplayName()]),
]);
$content .= elgg_view_form('group_tools/join_motivation', ['class' => 'mtm'], ['entity' => $group]);

echo elgg_view_module('info', $title, $content);
