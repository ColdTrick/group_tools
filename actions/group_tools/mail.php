<?php
/**
 * Mail all the members of a group
 */

$group_guid = (int) get_input('group_guid', 0);
$user_guids = (array) get_input('user_guids');
$all_members = (int) get_input('all_members');

$subject = get_input('title');
$body = get_input('description');

if (empty($group_guid) || empty($body) || (empty($user_guids) && empty($all_members))) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($group_guid);
if (!$group instanceof ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!group_tools_group_mail_enabled($group) && !group_tools_group_mail_members_enabled($group)) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if (!empty($all_members)) {
	$user_guids = $group->getMembers([
		'callback' => function($row) {
			return (int) $row->guid;
		},
		'limit' => false,
	]);
} else {
	foreach ($user_guids as $index => $user_guid) {
		$user_guids[$index] = (int) $user_guid;
	}
	
	$user_guids = array_filter(array_unique($user_guids));
}

$group_mail = new GroupMail();
$group_mail->container_guid = $group_guid;

$group_mail->title = $subject;
$group_mail->description = $body;

$group_mail->setRecipients($user_guids);

$group_mail->enqueue();

return elgg_ok_response('', elgg_echo('group_tools:action:mail:success'), $group->getURL());
