define(['jquery'], function ($) {
	$(document).on('click', '.group-tools-admin-add-tool-preset', function (e) {
		e.preventDefault();

		var $clone_base = $('#group-tools-tool-preset-base');
		var $clone = $clone_base.clone();

		$clone.removeAttr('id').removeClass('hidden');
		$clone.find('> div.hidden').removeClass('hidden');

		// find inputs and set correct name
		var counter = $clone_base.parent().find('> div').length;
		while ($clone_base.parent().find('input[name^="params[' + counter + ']"]').length) {
			counter++;
		}

		var $inputs = $clone.find(':input');
		$.each($inputs, function (index, object) {
			var name = $(object).attr('name');
			name = name.replace('params[i]', 'params[' + counter + ']');
			$(object).attr('name', name);
		});

		// insert clone
		$clone.insertBefore($clone_base);
	});

	$(document).on('click', '.group-tools-admin-edit-tool-preset', function (e) {
		e.preventDefault();
		
		var $container = $(this).closest('.group-tools-group-preset-wrapper').find('div[rel="edit"]');
		$container.toggleClass('hidden');
	});

	$(document).on('click', '.group-tools-admin-delete-tool-preset', function (e) {
		e.preventDefault();
		$(this).parent().parent().remove();
	});

	$(document).on('keyup keydown', '.group-tools-admin-change-tool-preset-title', function () {
		if (!$(this).val()) {
			return;
		}
		
		var $label = $(this).closest('.group-tools-group-preset-wrapper').find('label[rel="title"]');
		$label.html($(this).val());
	});

	$(document).on('keyup keydown', '.group-tools-admin-change-tool-preset-description', function () {
		if (!$(this).val()) {
			return;
		}
		
		var $container = $(this).closest('.group-tools-group-preset-wrapper').find('div[rel="description"]');
		$container.html($(this).val());
	});
});
