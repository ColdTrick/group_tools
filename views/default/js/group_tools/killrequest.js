define('group_tools/killrequest', function (require) {

	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');
	var lightbox = require('elgg/lightbox');
	
	$(document).on('submit', '.elgg-form-groups-killrequest', function () {
		var id = $(this).find('input[name="relationship_id"]').val();
		
		var ajax = new Ajax();
		ajax.action($(this).attr('action'), {
			data: ajax.objectify(this),
			success: function() {
				var $wrapper = $('#elgg-relationship-' + id);
				if ($wrapper.length) {
					$wrapper.remove();
				}
			},
			complete: function() {
				lightbox.close();
			}
		});

		return false;
	});
});
