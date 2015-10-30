<?php

$invite_site_members = elgg_extract("invite", $vars, "no");
$invite_email = elgg_extract("invite_email", $vars, "no");
$invite_csv = elgg_extract("invite_csv", $vars, "no");

$tabs = array(
	'friends' => array(
		'text' => elgg_echo('group_tools:group:invite:friends'),
		'href' => '#group-tools-invite-friends',
		'priority' => 200,
		'selected' => true,
		'class' => 'group-tools-invite-filter',
	)
);

if ($invite_site_members == 'yes') {
	$tabs['users'] = array(
		'text' => elgg_echo('group_tools:group:invite:users'),
		'href' => '#group-tools-invite-users',
		'priority' => 300,
	);
}

if ($invite_email == 'yes') {
	$tabs['email'] = array(
		'text' => elgg_echo('group_tools:group:invite:email'),
		'href' => '#group-tools-invite-email',
		'priority' => 400,
	);
}

if ($invite_csv == 'yes') {
	$tabs['csv'] = array(
		'text' => elgg_echo('group_tools:group:invite:csv'),
		'href' => '#group-tools-invite-csv',
		'priority' => 500,
	);
}

if (count($tabs) > 1) {
	foreach ($tabs as $name => $tab) {
		$tab['name'] = $name;
		elgg_register_menu_item('filter', $tab);
	}
}

echo elgg_view_menu('filter', array(
	'handler' => 'group_tools',
	'item_class' => 'group-tools-invite-tab',
	'class' => 'group-tools-invite-filter',
	'sort_by' => 'priority',
	'params' => array(
		'invite' => $invite_site_members,
		'invite_email' => $invite_email,
		'invite_csv' => $invite_csv,
	)
));