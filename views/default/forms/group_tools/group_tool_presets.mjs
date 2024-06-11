import 'jquery';

$(document).on('click', '.group-tools-admin-add-tool-preset', function (e) {
	e.preventDefault();

	var $clone_base = $('#group-tools-tool-preset-base');
	var $clone = $clone_base.clone();

	$clone.removeAttr('id').removeClass('hidden');
	$clone.find('.group-tools-group-preset-edit').removeClass('hidden');

	// find inputs and set correct name
	var counter = $clone_base.parent().find('> div.group-tools-group-preset-wrapper').length;
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
	$clone_base.parent().find('.group-tools-group-preset-edit:visible').addClass('hidden');
	$clone.insertBefore($clone_base);
});

$(document).on('click', '.group-tools-admin-edit-tool-preset', function (e) {
	e.preventDefault();
	
	var $container = $(this).closest('.group-tools-group-preset-wrapper').find('.group-tools-group-preset-edit');
	var visible = $container.is(':visible');
	
	$(this).closest('.elgg-form-body').find('.group-tools-group-preset-edit:visible').addClass('hidden');
	
	if (!visible) {
		$container.removeClass('hidden');
	}
});

$(document).on('click', '.group-tools-admin-delete-tool-preset', function (e) {
	e.preventDefault();
	
	$(this).closest('.elgg-form-body').find('.group-tools-group-preset-edit:visible').addClass('hidden');
	
	$(this).closest('.group-tools-group-preset-wrapper').remove();
});

$(document).on('keyup keydown', '.group-tools-admin-change-tool-preset-title', function () {
	if (!$(this).val()) {
		return;
	}
	
	var $label = $(this).closest('.group-tools-group-preset-wrapper').find('.group-tools-group-preset-title');
	$label.html($(this).val());
});

$(document).on('keyup keydown', '.group-tools-admin-change-tool-preset-description', function () {
	if (!$(this).val()) {
		return;
	}
	
	var $container = $(this).closest('.group-tools-group-preset-wrapper').find('.group-tools-group-preset-description');
	$container.html($(this).val());
});
