<?php
/**
 * Mail group members
 */

use Elgg\EntityPermissionsException;

elgg_gatekeeper();

$group_guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($group, 'group');

$group = get_entity($group_guid);

if (!group_tools_group_mail_enabled($group) && !group_tools_group_mail_members_enabled($group)) {
	throw new EntityPermissionsException();
}

elgg_require_js('group_tools/mail');

// set page owner
elgg_set_page_owner_guid($group->guid);
elgg_set_context('groups');

// set breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), 'groups/all');
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb(elgg_echo('group_tools:menu:mail'));

// get members
$members = $group->getMembers([
	'limit' => false,
]);

// build page elements
$title_text = elgg_echo('group_tools:mail:title');
$title = elgg_view_title($title_text);

$form_vars = [
	'id' => 'group_tools_mail_form',
	'class' => 'elgg-form-alt',
];
$body_vars = [
	'entity' => $group,
	'members' => $members,
];
$form = elgg_view_form('group_tools/mail', $form_vars, $body_vars);

// build page
$body = elgg_view_layout('content', [
	'entity' => $group,
	'title' => $title_text,
	'content' => $form,
	'filter' => false
]);

// draw page
echo elgg_view_page($title_text, $body);
