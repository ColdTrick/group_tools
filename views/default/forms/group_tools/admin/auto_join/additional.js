define(['jquery', 'elgg/Ajax', 'elgg/lightbox'], function($, Ajax, lightbox) {
	$(document).on('click', '#group-tools-auto-join-add-pattern', function (event){
		event.preventDefault();
		
		var ajax = new Ajax();
		ajax.view('group_tools/elements/auto_join_match_pattern', {
			success: function(data) {
				$('#group-tools-auto-join-add-pattern').before(data);
				lightbox.resize();
			}
		});
		
		return false;
	});
});
