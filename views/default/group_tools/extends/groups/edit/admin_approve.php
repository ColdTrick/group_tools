<?php

use Elgg\Database\QueryBuilder;

/* @var $entity \ElggGroup */
$entity = elgg_extract('entity', $vars);
if (empty($entity->guid) && elgg_is_admin_logged_in()) {
	// group create form
	return;
}

if (elgg_get_plugin_setting('admin_approve', 'group_tools') !== 'yes') {
	return;
}

if (empty($entity->guid)) {
	// create form
	echo elgg_view_message('notice', elgg_echo('group_tools:group:admin_approve:notice'));
} elseif ($entity->access_id === ACCESS_PRIVATE && !(bool) $entity->is_concept) {
	// group profile / edit form
	$message = elgg_echo('group_tools:group:admin_approve:notice:profile');
	
	if ($entity->canEdit() && (bool) elgg_get_plugin_setting('creation_reason', 'group_tools')) {
		$count = $entity->getAnnotations([
			'count' => true,
			'wheres' => [
				function(QueryBuilder $qb, $main_alias) {
					return $qb->compare("{$main_alias}.name", 'like', 'approval_reason:%', ELGG_VALUE_STRING);
				},
			],
		]);
		if (!empty($count)) {
			$message .= elgg_view('output/url', [
				'text' => elgg_echo('group_tools:group:admin_approve:reasons'),
				'href' => elgg_http_add_url_query_elements('ajax/view/group_tools/group/reasons', [
					'guid' => $entity->guid,
				]),
				'class' => 'mls elgg-lightbox',
			]);
		}
	}
	
	echo elgg_view_message('notice', $message);
}
