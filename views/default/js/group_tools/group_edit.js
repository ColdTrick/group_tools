define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	
	/** global: elgg */
	elgg.provide("elgg.group_tools");
	
	elgg.group_tools.toggle_featured = function(group_guid, element) {
		var action_type = "";
	
		if ($(element).val() == "yes") {
			action_type = "feature";
		}
	
		elgg.action("action/groups/featured", {
			data : {
				group_guid: group_guid,
				action_type: action_type
			}
		});
	};
	
	elgg.group_tools.toggle_special_state = function(state, group_guid) {
		elgg.action("action/group_tools/admin/toggle_special_state", {
			data : {
				group_guid: group_guid,
				state: state
			}
		});
	};
	
	elgg.group_tools.show_join_motivation = function(elem) {
		
		if ($(elem).val() === '0') {
			$('#group-tools-join-motivation').show();
		} else {
			$('#group-tools-join-motivation').hide();
		}
	};
	
	$(document).on('change', '#groups-owner-guid', function() {
		
		if (parseInt($(this).val()) === parseInt(elgg.get_logged_in_user_guid())) {
			$('.group-tools-admin-transfer-remain').addClass('hidden');
		} else {
			$('.group-tools-admin-transfer-remain').removeClass('hidden');
		}
	});

});
