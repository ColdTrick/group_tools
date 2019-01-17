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

});
