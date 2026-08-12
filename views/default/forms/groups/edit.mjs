import 'jquery';
import Ajax from 'elgg/Ajax';

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
}

function check_required_fields(event) {
	const $tab = $(this).parent();
	if ($tab.hasClass('elgg-state-selected') || $tab.parent().find('li.elgg-state-selected').length === 0) {
		// clicking on current selected tab
		// or no tab selected yet
		return;
	}
	
	// validate form
	const $form = $(this).closest('.elgg-form-groups-edit');
	const $active_section = $form.find('.elgg-tabs-content > .elgg-state-active');
	
	const $inputs = $active_section.find('input, select, textarea');
	let valid = true;
	$inputs.each(function(index, elem) {
		if (!elem.willValidate) {
			// this element will not be validated (eg button)
			return;
		}
		
		if ($(elem).is(':hidden')) {
			// input is not shown (eg conditional section)
			return;
		}
		
		if (!elem.reportValidity()) {
			valid = false;
			
			return false;
		}
	});
	
	if (!valid) {
		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();
		
		return false;
	}
}

var inputTimeout;
$(document).on('input', 'form.elgg-form-groups-edit input[name="name"]', function(event) {
	clearTimeout(inputTimeout);
	if ($(this).val().length < 3) {
		// not enough characters (yet)
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

$('body').on('click', '.elgg-form-groups-edit .elgg-components-tab a', check_required_fields); // register on body to be before tab switch in page/components/tabs.js
