import 'jquery';
import spinner from 'elgg/spinner';

$(document).on('change', 'input[name="checkall"]', function () {
	var $form = $(this).closest('form');
	var checked = $(this).is(":checked");
	$('[name="group_guids[]"]', $form).prop('checked', checked);
});

$(document).on('submit', '#group-tools-admin-bulk-delete', function() {
	spinner.start();
});
