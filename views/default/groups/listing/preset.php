<?php
/**
 * Renders a list of groups based on group tool preset
 *
 * Pass filter=preset and preset=<name of your preset> in your input to get a list of groups with the preset
 * Pass include_undefined=1 to include all groups without a preset configured
 */

use Elgg\Database\MetadataTable;
use Elgg\Database\QueryBuilder;

$preset = get_input('preset');
if (empty($preset)) {
	throw new \Elgg\Exceptions\Http\BadRequestException();
}

$preset_options = [];
if ((bool) get_input('include_undefined', false)) {
	$preset_options['wheres'] = [
		function (QueryBuilder $qb, $main_alias) use ($preset) {
			$subquery = $qb->subquery(MetadataTable::TABLE_NAME);
			$subquery->select('entity_guid')
				->where($qb->compare('name', '=', 'group_tools_preset', ELGG_VALUE_STRING));

			$nothaving = $qb->compare("{$main_alias}.guid", 'NOT IN', $subquery->getSQL());
			
			$subquery = $qb->subquery(MetadataTable::TABLE_NAME);
			$subquery->select('entity_guid')
				->where($qb->compare('name', '=', 'group_tools_preset', ELGG_VALUE_STRING))
				->andWhere($qb->compare('value', '=', $preset, ELGG_VALUE_STRING));
			
			$having = $qb->compare("{$main_alias}.guid", 'IN', $subquery->getSQL());
			
			return $qb->merge([$nothaving, $having], 'OR');
		},
	];
} else {
	$preset_options['metadata_name_value_pairs'] = [
		'group_tools_preset' => $preset,
	];
}

$options = (array) elgg_extract('options', $vars);
$vars['options'] = array_merge($options, $preset_options);

echo elgg_view('groups/listing/all', $vars);
