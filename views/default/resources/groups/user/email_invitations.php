<?php
/**
 * List all e-mail invitations to a user.
 * This will show a list of groups for which the current user has been invited based on e-mail address
 */

$user = elgg_get_page_owner_entity();

elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));

// build page elements
$content = elgg_view('group_tools/invitationrequests/emailinvitations', ['entity' => $user]);
$content .= elgg_view('group_tools/invitationrequests/emailinviteform', ['entity' => $user]);

// draw page
echo elgg_view_page(elgg_echo('collection:annotation:email_invitation:user'), [
	'content' => $content,
	'filter_id' => 'groups/invitations',
	'filter_value' => 'email_invitations',
]);
