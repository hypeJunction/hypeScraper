require(['elgg', 'jquery', 'elgg/spinner', 'elgg/ready'], function (elgg, $, spinner) {
	$(document).on('click', '.scraper-play-button', function (e) {
		e.preventDefault();
		var $elem = $(this);
		elgg.getJSON($elem.data('href'), {
			beforeSend: spinner.start,
			success: function (data) {
				if (data && data.html) {
					var $block = $elem.closest('.scraper-card-block');
					$block.addClass('scraper-card-flex').html($(data.html));
					$block.css('min-height', $block.innerWidth() * 9 / 16);
				}
			},
			complete: spinner.stop,
		});
	});
});


