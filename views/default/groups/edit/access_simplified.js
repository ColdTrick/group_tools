define(function (require) {

	var $ = require('jquery');
	
	var update_access_values = function (group_type) {
		
		var $form = $('.elgg-form-groups-edit');
		var $membership = $form.find('[name="membership"]');
		var $content_access_mode = $form.find('[name="content_access_mode"]');
		var $group_default_access = $form.find('[name="content_default_access"]');
		
		if (group_type == 'open') {
			$membership.val('2');
			$content_access_mode.val('unrestricted');
			$group_default_access.prop('disabled', false);
		} else {
			// closed
			$membership.val('0');
			$content_access_mode.val('members_only');
			$group_default_access.prop('disabled', true);
		}		
	};

	$('#group-tools-group-edit-access .group-tools-simplified-option').on('click', function() {
		$(this).siblings().removeClass('elgg-state-active');
		
		$(this).addClass('elgg-state-active');
		
		update_access_values($(this).data('groupType'));
	});
	
	$('#group-tools-group-edit-access .group-tools-simplified-option:first').click();
});
