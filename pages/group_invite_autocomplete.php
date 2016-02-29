<?php
/**
 * jQuery procedure to fill an autocomplete dropdown
 */

$q = sanitize_string(get_input('q'));
$current_users = sanitize_string(get_input('user_guids'));
$limit = (int) get_input('limit', 50);
$group_guid = (int) get_input('group_guid', 0);
$relationship = sanitize_string(get_input('relationship', 'none'));

$include_self = get_input('include_self', false);
if (!empty($include_self)) {
	$include_self = true;
}

$user = elgg_get_logged_in_user_entity();
$result = [];

header('Content-Type: application/json');

if (empty($user) || empty($q) || empty($group_guid)) {
	echo json_encode(array_values($result));
	exit();
}

$users_found = [];

// show hidden (unvalidated) users
$hidden = access_show_hidden_entities(true);

if ($relationship != 'email') {
	$dbprefix = elgg_get_config('dbprefix');
	
	// find existing users
	$query_options = [
		'type' => 'user',
		'limit' => $limit,
		'joins' => [
			"JOIN {$dbprefix}users_entity u ON e.guid = u.guid",
		],
		'wheres' => [
			"(u.name LIKE '%{$q}%' OR u.username LIKE '%{$q}%')",
			'u.banned = "no"',
		],
		'order_by' => 'u.name asc'
	];
	
	if (!$include_self) {
		if (empty($current_users)) {
			$current_users = $user->getGUID();
		} else {
			$current_users .= ',' . $user->getGUID();
		}
	}
	
	if (!empty($current_users)) {
		$query_options['wheres'][] = "e.guid NOT IN ({$current_users})";
	}
	
	if ($relationship == 'friends') {
		$query_options['relationship'] = 'friend';
		$query_options['relationship_guid'] = $user->getGUID();
	} elseif ($relationship == 'site') {
		$query_options['relationship'] = 'member_of_site';
		$query_options['relationship_guid'] = elgg_get_site_entity()->getGUID();
		$query_options['inverse_relationship'] = true;
	}
	
	$users_found = elgg_get_entities_from_relationship($query_options);
} else {
	// invite by email
	$regexpr = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
	if (preg_match($regexpr, $q)) {
		// only start matching if $q is an emailaddress
		
		$users_found = get_user_by_email($q);
		if (empty($users_found)) {
			$result[] = [
				'type' => 'email',
				'value' => $q,
				'label' => $q,
				'content' => $q,
			];
		}
	}
}

foreach ($users_found as $user) {
	$content = elgg_view('output/img', [
		'src' => $user->getIconURL('tiny'),
		'alt' => $user->name,
		'class' => 'mrs',
	]);
	$content .= $user->name;
	$is_member = false;
	if (check_entity_relationship($user->getGUID(), 'member', $group_guid)) {
		$is_member = true;
		$content .= ' - ' . elgg_format_element('span', ['class' => 'elgg-subtext'], elgg_echo('group_tools:groups:invite:user_already_member'));
	}
	
	$result[] = [
		'type' => 'user',
		'member' => $is_member,
		'value' => $user->getGUID(),
		'label' => $user->name,
		'content' => $content,
		'name' => $user->name,
	];
}

// restore hidden users
access_show_hidden_entities($hidden);

echo json_encode(array_values($result));

exit();
