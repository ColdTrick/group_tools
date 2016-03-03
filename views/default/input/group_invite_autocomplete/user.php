<?php
/**
 * User view for in the gorup invite autocomplete
 */

$name = elgg_extract('inputname', $vars);
$user = elgg_extract('user', $vars);
$group_guid = (int) elgg_extract('group_guid', $vars);

$content = elgg_view_entity_icon($user, 'tiny', [
	'use_hover' => false,
	'class' => 'mrs float',
]);

$content .= $user->name;
if (check_entity_relationship($user->getGUID(), 'member', $group_guid)) {
	$content .= elgg_format_element('div', ['class' => 'elgg-subtext'], elgg_echo('group_tools:groups:invite:user_already_member'));
}

?>
<div class="group_tools_group_invite_autocomplete_autocomplete_result elgg-discover_result elgg-discover clearfix">
	<input type="hidden" value="<?php echo $user->getGUID(); ?>" name="<?php echo $name; ?>" />
	
	<?php echo $content; ?>
	<?php echo elgg_view_icon('delete-alt', 'elgg-discoverable float-alt'); ?>
</div>
