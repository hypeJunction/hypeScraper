<?php

return [
	'scraper:settings:linkify' => 'Linkify longtext output',
	'scraper:settings:linkify:help' => 'Automatically wrap URLs, usernames, hashtags, and emails in longtext output view',

	'scraper:settings:bookmarks' => 'Add bookmark previews',
	'scraper:settings:bookmarks:help' => 'Add bookmark previews to river and full page view',

	'admin:upgrades:scraper:move_to_db' => 'Upgrade scraped URLs',
	'admin:upgrades:scraper:move_to_db:description' => '
		Scraped URL information is now stored in the database.
		This upgrade script will move URL information to the database, recreate preview images using a more sophisticated approach,
		and clean up left over information from the disk storage.
	',

	'admin:scraper' => 'Scraper',
	'admin:scraper:preview' => 'Preview URL Cards',
	'admin:scraper:preview:url' => 'URL',

	'scraper:settings:oembed_domains' => 'oEmbed domain whitelist',
	'scraper:settings:oembed_domains:help' => '
		List domains (one per line) whitelisted for oEmbed parsing.
		When users share content from domains listed here, the oEmbed content will be displayed in the player view,
		e.g. when a YouTube video is shared, the card view will contain a link to display an embedded player.
	',

];