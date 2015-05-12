//<script>
	elgg.provide('elgg.scraper');
	elgg.scraper.init = function () {
		$('.scraper-play-button').live('click', function (e) {
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
	}
	elgg.register_hook_handler('init', 'system', elgg.scraper.init);

