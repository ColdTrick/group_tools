<?php 

	$group = $vars["entity"];
	$members = $vars["members"];
	
	$friendpicker_value = array();
	if(!empty($members)){
		foreach($members as $member){
			$friendpicker_value[] = $member->getGUID();
		}
	}
	
	$form_data = elgg_view("input/hidden", array("internalname" => "group_guid", "value" => $group->getGUID()));
	
	$form_data .= "<label>" . elgg_echo("group_tools:mail:form:recipients") . ": <span id='group_tools_mail_recipients_count'>" . count($friendpicker_value) . "</span></label>";
	$form_data .= "<br />";
	$form_data .= "<a href='javascript:void(0);' onclick='$(\"#group_tools_mail_member_selection\").toggle();'>" . elgg_echo("group_tools:mail:form:members:selection") . "</a>";
	
	$form_data .= "<div id='group_tools_mail_member_selection'>";
	$form_data .= elgg_view("friends/picker", array("entities" => $members, "value" => $friendpicker_value, "highlight" => "all", "internalname" => "user_guids"));
	$form_data .= "</div>";
	
	$form_data .= "<div id='group_tools_mail_member_options'>";
	$form_data .= elgg_view("input/button", array("type" => "button", "value" => elgg_echo("group_tools:clear_selection"), "js" => "onclick='group_tools_mail_clear_members();'"));
	$form_data .= elgg_view("input/button", array("type" => "button", "value" => elgg_echo("group_tools:all_members"), "js" => "onclick='group_tools_mail_all_members();'"));
	$form_data .= "<br />";
	$form_data .= "</div>";
	
	
	$form_data .= "<label>" . elgg_echo("group_tools:mail:form:title") . "</label>";
	$form_data .= elgg_view("input/text", array("internalname" => "title"));
	
	$form_data .= "<label>" . elgg_echo("group_tools:mail:form:description") . "</label>";
	$form_data .= elgg_view("input/longtext", array("internalname" => "description"));

	$form_data .= "<div>";
	$form_data .= elgg_view("input/submit", array("value" => elgg_echo("send")));
	$form_data .= "</div>";

	$form = elgg_view("input/form", array("body" => $form_data,
											"action" => $vars["url"] . "action/group_tools/mail",
											"internalid" => "group_tools_mail_form"));
	
	echo elgg_view("page_elements/contentwrapper", array("body" => $form));

?>
<script type="text/javascript">
	$(document).ready(function(){
		$('#group_tools_mail_form').submit(function(){
			var result = false;
			var error_msg = "";
			var error_count = 0;

			if($('#group_tools_mail_member_selection input[name="user_guids[]"]:checked').length == 0){
				error_msg += "<?php echo elgg_echo("group_tools:mail:form:js:members"); ?>\n";
				error_count++;
			}
			
			if($(this).find('input[name="description"]').val() == ""){
				error_msg += "<?php echo elgg_echo("group_tools:mail:form:js:description"); ?>\n";
				error_count++;
			}

			if(error_count > 0){
				alert(error_msg);
			} else {
				result = true;
			}
			
			return result;
		});

		$('#group_tools_mail_member_selection input[type=checkbox]').live("change", group_tools_mail_update_recipients);
	});

	function group_tools_mail_clear_members(){
		$('#group_tools_mail_member_selection input[name="user_guids[]"]:checked').each(function(){
			$(this).removeAttr('checked');
		});

		group_tools_mail_update_recipients();
	}
	
	function group_tools_mail_all_members(){
		$('#group_tools_mail_member_selection input[name="user_guids[]"]').each(function(){
			$(this).attr('checked', 'checked');
		});

		group_tools_mail_update_recipients();
	}

	function group_tools_mail_update_recipients(){
		var count = $('#group_tools_mail_member_selection input[name="user_guids[]"]:checked').length;

		$('#group_tools_mail_recipients_count').html(count);
	}
</script>