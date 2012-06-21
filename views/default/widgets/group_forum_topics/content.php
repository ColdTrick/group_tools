<?php
	$widget = $vars["entity"];
	$group = $widget->getOwnerEntity();
		
	$topic_count = sanitise_int($widget->topic_count, false);
	if(empty($topic_count)){
		$topic_count = 4;
	}
	
	$options = array(
		'type' => 'object',
		'subtype' => 'groupforumtopic',
		'container_guid' => $group->getGUID(),
		'order_by' => 'e.last_action desc',
		'limit' => $topic_count,
		'full_view' => false,
		'pagination' => false,
	);
	
	if($content = elgg_list_entities($options)){
		echo $content;
	} else {
		echo elgg_echo("discussion:none");
	}
	    
    $new_link = elgg_view('output/url', array(
    	'href' => "discussion/add/" . $group->getGUID(),
    	'text' => elgg_echo('groups:addtopic'),
    	'is_trusted' => true,
    ));
    
    echo "<div>" . $new_link . "</div>";