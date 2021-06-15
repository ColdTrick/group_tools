define(['jquery', 'elgg', 'elgg/Ajax'], function($, elgg, Ajax) {
	/** global: elgg */
	elgg.provide("elgg.group_tools");
			
	elgg.group_tools.show_join_motivation = function(elem) {
		
		if ($(elem).val() === '0') {
			$('#group-tools-join-motivation').show();
		} else {
			$('#group-tools-join-motivation').hide();
		}
	};

	$(document).on('blur', 'form.elgg-form-groups-edit input[name="name"]', function(event) {
		var $input = $(this);
		if ($input.closest('form').find('input[name="group_guid"]').length) {
			// edit
			return;
		}
		
		var ajax = new Ajax();
		ajax.view('group_tools/group/suggested', {
			data: {
				q: $input.val()
			},
			success: function (data) {
				$('#group-tools-edit-group-suggestions').remove();
				
				$input.closest('.elgg-field').after(data);
			}
		});
	});
});
