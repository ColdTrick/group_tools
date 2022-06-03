<?php

use Elgg\Exceptions\Http\EntityPermissionsException;

$group = elgg_get_page_owner_entity();

elgg_entity_gatekeeper($group->guid, 'group');

// get plugin settings
$invite_friends = elgg_get_plugin_setting('invite_friends', 'group_tools') === 'yes';
$invite = elgg_get_plugin_setting('invite', 'group_tools') === 'yes';
$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools') === 'yes';
$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools') === 'yes';

if ($invite || $invite_csv || $invite_email) {
	$title = elgg_echo('group_tools:groups:invite:title');
} elseif ($invite_friends) {
	$title = elgg_echo('groups:invite:title');
} else {
	// no option is allowed
	$ex = new EntityPermissionsException(elgg_echo('group_tools:groups:invite:error'));
	$ex->setRedirectUrl($group->getURL());
	
	throw $ex;
}

// breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());

$content = elgg_view_form('groups/invite', [
	'id' => 'invite_to_group',
	'class' => 'elgg-form-alt mtm',
	'prevent_double_submit' => false,
], [
	'entity' => $group,
	'invite_friends' => $invite_friends,
	'invite' => $invite,
	'invite_email' => $invite_email,
	'invite_csv' => $invite_csv,
]);

// draw page
echo elgg_view_page($title, [
	'content' => $content,
	'filter' => false,
]);
