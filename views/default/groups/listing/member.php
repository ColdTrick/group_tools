<?php
/**
 * Renders a list of groups which the user is a member of
 *
 * @uses $vars['options'] Additional listing options
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\RelationshipsTable;

elgg_gatekeeper();

$options = (array) elgg_extract('options', $vars);

$member_options = [
	'relationship' => 'member',
	'relationship_guid' => elgg_get_logged_in_user_guid(),
	'inverse_relationship' => false,
];

$vars['options'] = array_merge($options, $member_options);

echo elgg_view('groups/listing/all', $vars);
