<?php
/**
 * Upload a csv file to invite users to a group
 *
 * used in /forms/groups/invite
 */

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:invite:csv:description'),
]);

echo elgg_view_field([
	'#type' => 'file',
	'name' => 'csv',
]);
