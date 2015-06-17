<?php
/**
 * Mail all the members of a group
 */

$group_guid = (int) get_input("group_guid", 0);
$user_guids = (array) get_input("user_guids");
$subject = get_input("title");
$body = get_input("description");

array_walk($user_guids, 'sanitise_int');

if (empty($group_guid) || empty($body) || empty($user_guids)) {
	register_error(elgg_echo("group_tools:action:error:input"));
	forward(REFERER);
}

$group = get_entity($group_guid);
if (empty($group) || !($group instanceof ElggGroup)) {
	register_error(elgg_echo("group_tools:action:error:entity"));
	forward(REFERER);
}

if (!group_tools_group_mail_enabled($group) && !group_tools_group_mail_members_enabled($group)) {
	register_error(elgg_echo("group_tools:action:error:edit"));
	forward($group->getURL());
}

// send mail
set_time_limit(0);

// limit to group members
$options = array(
	'type' => 'user',
	'guids' => $user_guids,
	'limit' => false,
	'relationship' => 'member',
	'relationship_guid' => $group->getGUID(),
	'inverse_relationship' => true
);
$batch = new ElggBatch('elgg_get_entities_from_relationship', $options);

// add footer telling the message is from group
$body .= PHP_EOL . PHP_EOL;
$body .= elgg_echo("group_tools:mail:message:from") . ": " . $group->name . " [" . $group->getURL() . "]";

// notify all members
foreach ($batch as $user) {
	notify_user($user->getGUID(), $group->getGUID(), $subject, $body, NULL, "email");
}

system_message(elgg_echo("group_tools:action:mail:success"));

forward($group->getURL());
