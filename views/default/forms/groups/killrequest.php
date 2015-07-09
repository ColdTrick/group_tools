<?php

$group = elgg_extract('group', $vars);
$user = elgg_extract('user', $vars);

if (!($group instanceof ElggGroup)) {
	return;
}

if (!($user instanceof ElggUser)) {
	return;
}

$content = elgg_view('input/hidden', array('name' => 'group_guid', 'value' => $group->getGUID()));
$content .= elgg_view('input/hidden', array('name' => 'user_guid', 'value' => $user->getGUID()));

$content .= elgg_echo('group_tools:groups:membershipreq:kill_request:prompt');
$content .= elgg_view('input/plaintext', array(
	'name' => 'reason',
	'rows' => '3',
	'class' => 'mbm'
));

$content .= '<div class="elgg-foot">';
$content .= elgg_view('input/button', array(
	'value' => elgg_echo('cancel'),
	'class' => 'elgg-button-cancel float-alt',
	'onclick' => '$.colorbox.close();'
));
$content .= elgg_view('input/submit', array(
	'value' => elgg_echo('decline')
));
$content .= '</div>';

echo elgg_view_module('info', elgg_echo('groups:joinrequest:remove:check'), $content);