<?php
/**
 * Group view in Group Picker
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['entity'] Group entity
 * @uses $vars['input_name'] Name of the returned data array
 */

/* @var ElggEntity $entity */
$entity = $vars['entity'];
$input_name = $vars['input_name'];


$icon = elgg_view_entity_icon($entity, 'tiny', array('use_hover' => false));

$name = $entity->name;
if ($name == '') {
	$name = $entity->title;
}

?>
<li data-guid='<?php echo $entity->guid ?>'>
	<div class='elgg-image-block'>
		<div class='elgg-image'><?php echo $icon ?></div>
		<div class='elgg-image-alt'><span class="elgg-icon elgg-icon-delete-alt elgg-group-picker-remove"></span></div>
		<div class='elgg-body'><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
	</div>
	<input type="hidden"
		   name="<?php echo htmlspecialchars($input_name, ENT_QUOTES, 'UTF-8'); ?>[]"
		   value="<?php echo $entity->guid ?>">
</li>