define(['elgg', 'jquery'], function (elgg, $) {

	$(document).on('click', '.scraper-play-button', function (e) {
		e.preventDefault();
		var $elem = $(this);
		elgg.getJSON($elem.data('href'), {
			beforeSend: function () {
				$elem.addClass('elgg-state-loading');
			},
			success: function (data) {
				if (data && data.html) {
					$elem.closest('.scraper-card-block').addClass('scraper-card-flex').html($(data.html));
				}
			},
			complete: function () {
				$elem.removeClass('elgg-state-loading');
			}
		});
	});
});


