<?php

	$widget = elgg_extract("entity", $vars);
	
	$limit = (int) $widget->num_display;
	if($limit < 1){
		$limit = 5;
	}
	
	$options = array(
		'type' => 'object',
		'subtype' => 'groupforumtopic',
		'order_by' => 'e.last_action desc',
		'limit' => $limit,
		'full_view' => false,
	);
	
	if(!($content = elgg_list_entities($options))){
		$content = elgg_echo("grouptopic:notcreated");
	} else {
		$content .= "<div class='elgg-widget-more'>";
		$content .= elgg_view("output/url", array("text" => elgg_echo("widgets:index_discussions:more"), "href" => $vars["url"] . "discussion/all"));
		$content .= "</div>";
	}
	
	echo $content;