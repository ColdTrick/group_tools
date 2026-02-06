<?php
/**
 * Show a notice that the group is in concept
 *
 * @uses $vars['entity'] the group
 */

use Elgg\Values;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggGroup || !(bool) $entity->is_concept) {
	return;
}

if ($entity->canEdit()) {
	$content = elgg_echo('group_tools:group:concept:profile:description');
} else {
	$content = elgg_echo('group_tools:group:concept:profile:description:user');
}

$retention = (int) elgg_get_plugin_setting('concept_groups_retention', 'group_tools');
if ($retention > 0) {
	$retention_ts = Values::normalizeTime($entity->time_created);
	$retention_ts->modify("+{$retention} days");
	
	$friendly_ts = elgg_get_friendly_time($retention_ts->getTimestamp());
	
	$content .= ' ' . elgg_echo('group_tools:group:concept:profile:retention', [$friendly_ts]);
}

echo elgg_view_message('notice', $content);
