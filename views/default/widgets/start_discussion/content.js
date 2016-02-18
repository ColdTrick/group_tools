define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');

	$(document).on('submit', '#group-tools-start-discussion-widget-form', function () {
		var selected_group = $('#group-tools-discussion-quick-start-group').val();
		if (selected_group !== "0") {
			$('#group-tools-discussion-quick-start-access_id option').removeAttr("selected");
			$('#group-tools-discussion-quick-start-access_id option').each(function (index, elem) {
				if ($(elem).html() === selected_group) {
					$(elem).attr("selected", "selected");
				}
			});
		} else {
			elgg.register_error(elgg.echo("group_tools:forms:discussion:quick_start:group:required"));
			return false;
		}
	});
});