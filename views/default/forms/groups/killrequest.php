<?php

use Elgg\Exceptions\Http\BadRequestException;

elgg_import_esm('forms/groups/killrequest');

$relationship_id = (int) elgg_extract('relationship_id', $vars);

$relationship = elgg_get_relationship($relationship_id);
if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'membership_request') {
	throw new BadRequestException();
}

$user = get_entity($relationship->guid_one);
$group = elgg_call(ELGG_IGNORE_ACCESS, function() use ($relationship) {
	return get_entity($relationship->guid_two);
});
if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
	throw new BadRequestException();
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_guid',
	'value' => $group->guid,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'user_guid',
	'value' => $user->guid,
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'relationship_id',
	'value' => $relationship->id,
]);

$content = elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('group_tools:groups:membershipreq:kill_request:prompt'),
	'name' => 'reason',
	'rows' => '3',
]);

echo elgg_view_module('info', elgg_echo('groups:joinrequest:remove:check'), $content);

$footer = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'submit',
			'text' => elgg_echo('decline'),
		],
		[
			'#type' => 'button',
			'text' => elgg_echo('cancel'),
			'onclick' => 'import("elgg/lightbox").then((lightbox) => {lightbox.default.close();});',
		],
	],
]);

elgg_set_form_footer($footer);
