define(['jquery', 'elgg/Ajax'], function($, Ajax) {
			
	function add_group_suggestions() {
		var $input = $('form.elgg-form-groups-edit input[name="name"]');
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
	};
	
	var inputTimeout;
	$(document).on('input', 'form.elgg-form-groups-edit input[name="name"]', function(event) {
		clearTimeout(inputTimeout);
		if ($(this).val().length < 3) {
			// not enought characters (yet)
			return;
		}
		
		inputTimeout = setTimeout(add_group_suggestions, 400);
	});
	
	$(document).on('change', '#groups-membership', function() {
		if ($(this).val() === '0') {
			$('#group-tools-join-motivation').show();
		} else {
			$('#group-tools-join-motivation').hide();
		}
	});
});
