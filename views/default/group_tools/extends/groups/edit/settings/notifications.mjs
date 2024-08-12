import 'jquery';
import elgg from 'elgg';
import spinner from 'elgg/spinner';

$(document).on('click', '#group-tools-edit-notifications', function (event) {
	var $form = $(this).closest('form');
	$form.prop('action', elgg.normalize_url('action/group_tools/admin/notifications'));
	spinner.start();
	$form.submit();
});
