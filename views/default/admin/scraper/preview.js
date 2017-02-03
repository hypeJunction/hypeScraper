define(function(require) {

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	$(document).on('submit', '.elgg-form-admin-scraper-preview', function(e) {
		e.preventDefault();

		var $form = $(this);
		ajax.view('output/card', {
			data: ajax.objectify($form),
		}).done(function(output) {
			$('#scraper-preview').html($(output));
		});
	});
});