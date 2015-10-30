<?php
$group = elgg_extract('entity', $vars);
?>
<div>
	<label><?php echo elgg_echo('group_tools:group:invite:csv:label') ?></label>
	<span class='elgg-text-help'>
		<?php echo elgg_echo('group_tools:group:invite:csv:description') ?>
	</span>
	<?php
	echo elgg_view("input/file", array(
		'name' => 'csv'
	));
	?>
</div>