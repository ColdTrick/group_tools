define(function(require) {
	var $ = require('jquery');
	var elgg = require('elgg');
	
	/** global: elgg */
	elgg.provide("elgg.group_tools");
			
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
