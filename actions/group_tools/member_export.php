<?php
/**
 * Export the members of a group to CSV
 */

use Elgg\Database\QueryBuilder;

$group_guid = (int) get_input('group_guid');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup || !$group->canEdit() || elgg_get_plugin_setting('member_export', 'group_tools') !== 'yes') {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$group_admins = [];
if (group_tools_multiple_admin_enabled()) {
	$group_admins = elgg_get_entities([
		'type' => 'user',
		'limit' => false,
		'relationship' => 'group_admin',
		'relationship_guid' => $group->guid,
		'inverse_relationship' => true,
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) use ($group) {
				return $qb->compare("{$main_alias}.guid", '!=', $group->owner_guid, ELGG_VALUE_GUID);
			},
		],
		'callback' => function($row) {
			return (int) $row->guid;
		},
	]);
}

// create temp file
$temp_file = new ElggTempFile();
$fh = $temp_file->open('write');

// write header line
$headers = [
	'displayname',
	'username',
	'email',
	'banned',
	'member since (unix)',
	'member since (YYYY-MM-DD HH:MM:SS)',
	'role',
];
$profile_fields = elgg()->fields->get('user', 'user');
if (!empty($profile_fields)) {
	foreach ($profile_fields as $field_config) {
		$metadata_name = elgg_extract('name', $field_config);
		
		$lan_key = "profile:{$metadata_name}";
		$header = $metadata_name;
		if (elgg_language_key_exists($lan_key)) {
			$header = elgg_echo($lan_key);
		}
		
		$header = html_entity_decode($header);
		$header = str_ireplace("\"", " ", str_ireplace(PHP_EOL, "", $header));
		$headers[] = $header;
	}
}
fputcsv($fh, $headers, ';');

// get members
$members = new ElggBatch('elgg_get_entities', [
	'type' => 'user',
	'limit' => false,
	'relationship' => 'member',
	'relationship_guid' => $group->guid,
	'inverse_relationship' => true,
]);

/* @var $member ElggUser */
foreach ($members as $member) {
	// basic user information
	$info = [
		$member->getDisplayName(),
		$member->username,
		$member->email,
		$member->banned,
	];
	
	// member since
	$member_since = 0;
	
	$relationship = check_entity_relationship($member->guid, 'member', $group->guid);
	if ($relationship instanceof ElggRelationship) {
		$member_since = $relationship->time_created;
	}
	
	$info[] = $member_since;
	$info[] = date('Y-m-d G:i:s', $member_since);
	
	// role
	if ($group->owner_guid === $member->guid) {
		// owner
		$info[] = 'owner';
	} elseif (in_array($member->guid, $group_admins)) {
		$info[] = 'group admin';
	} else {
		$info[] = 'member';
	}
	
	// profile fields
	if (!empty($profile_fields)) {
		foreach ($profile_fields as $field_config) {
			$metadata_name = elgg_extract('name', $field_config);
			
			$value = $member->$metadata_name;
			if (is_array($value)) {
				$value = implode(', ', $value);
			}
			
			$value = html_entity_decode($value);
			$value = str_ireplace("\"", " ", str_ireplace(PHP_EOL, "", $value));
			$info[] = $value;
		}
	}
	
	fputcsv($fh, $info, ';');
}

// read the csv in to a var before output
$temp_file->close();

$contents = $temp_file->grabFile();

// cleanup the temp file
$temp_file->delete();

// output the csv
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . elgg_get_friendly_title($group->getDisplayName()) . '.csv"');
header('Content-Length: ' . strlen($contents));

echo $contents;
exit();
