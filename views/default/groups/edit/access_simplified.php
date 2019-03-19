<?php

elgg_require_js('groups/edit/access_simplified');

$open_text = '<h3>' . elgg_echo('group_tools:edit:access_simplified:open') . '</h3>';
$open_text .= elgg_view('output/longtext', ['value' => elgg_echo('group_tools:edit:access_simplified:open:description')]);

$closed_text = '<h3>' . elgg_echo('group_tools:edit:access_simplified:closed') . '</h3>';
$closed_text .= elgg_view('output/longtext', ['value' => elgg_echo('group_tools:edit:access_simplified:closed:description')]);

$open_text = elgg_format_element('div', [
	'class' => 'group-tools-simplified-option elgg-state-active',
	'data-group-type' => 'open',
], $open_text);

$closed_text = elgg_format_element('div', [
	'class' => 'group-tools-simplified-option',
	'data-group-type' => 'closed',
], $closed_text);

echo '<div class="group-tools-simplified-options clearfix">';
echo $open_text . $closed_text;
echo '</div>';

echo elgg_format_element('div', ['class' => 'hidden'] , elgg_view('groups/edit/access', $vars));
