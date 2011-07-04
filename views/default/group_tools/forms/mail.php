<?php 

	$group = $vars["entity"];
	
	$form_data = elgg_view("input/hidden", array("internalname" => "group_guid", "value" => $group->getGUID()));
	
	$form_data .= "<label>" . elgg_echo("group_tools:mail:form:title") . "</label>";
	$form_data .= elgg_view("input/text", array("internalname" => "title"));
	
	$form_data .= "<label>" . elgg_echo("group_tools:mail:form:description") . "</label>";
	$form_data .= elgg_view("input/longtext", array("internalname" => "description"));

	$form_data .= "<div>";
	$form_data .= elgg_view("input/submit", array("value" => elgg_echo("send")));
	$form_data .= "</div>";

	$form = elgg_view("input/form", array("body" => $form_data,
											"action" => $vars["url"] . "action/group_tools/mail"));
	
	echo elgg_view("page_elements/contentwrapper", array("body" => $form));

?>