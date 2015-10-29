define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');

	$(document).on('submit', '.elgg-form-groups-killrequest', function () {

		if (typeof $.colorbox !== 'undefined') {
			$.colorbox.close();
		}
		
		var guid = $(this).data('guid');

		elgg.action($(this).attr('action'), {
			data: $(this).serialize(),
			success: function () {
				var $wrapper = $('li.elgg-item[data-guid="' + guid + '"]');
				if ($wrapper.length) {
					$wrapper.remove();
				}
			}
		});

		return false;
	});
});