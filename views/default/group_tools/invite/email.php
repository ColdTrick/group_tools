<?php
$group = elgg_extract('entity', $vars);
?>

<div>
	<label><?php echo elgg_echo('group_tools:group:invite:email:label') ?></label>
	<span class='elgg-text-help'>
		<?php echo elgg_echo('group_tools:group:invite:email:description') ?>
	</span>
	<?php
	echo elgg_view('input/plaintext', array(
		'name' => 'emails',
	));
	?>
</div>