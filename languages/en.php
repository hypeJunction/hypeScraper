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
	'admin:scraper:edit' => 'Edit Card',
	'scraper:player:html' => 'Player HTML',
	
	'scraper:settings:oembed_domains' => 'oEmbed domain whitelist',
	'scraper:settings:oembed_domains:help' => '
		List domains (one per line) whitelisted for oEmbed parsing.
		When users share content from domains listed here, the oEmbed content will be displayed in the player view,
		e.g. when a YouTube video is shared, the card view will contain a link to display an embedded player.
	',

	'scraper:refetch' => 'Refetch new data from the URL (will erase any modifications made locally)',
	'scraper:refetch:confirm' => 'Refetch will erase existing URL information, preview images and any modifications you have made to it',
	'scraper:refetch:success' => 'URL has been successfully refetched',
	'scraper:refetch:error' => 'URL could not be refetched',

	'admin:scraper:cache' => 'Scraper Cache',
	'admin:scraper:cache:domain' => 'Cached Domain/URL',
	'admin:scraper:cache:find' => 'Find Cached URLs',
	'admin:scraper:cache:clear' => 'Clear Cache',
	'admin:scraper:cache:no_results' => 'No URLs were cached in this domain',

	'admin:scraper:hotfixes' => 'Hotfixes',
	'admin:scraper:hotfix' => 'Fix',

	'admin:scraper:timestamp_images' => 'Image timestamp hotfix',
	'admin:scraper:timestamp_images:help' => 'Image previews served from data store require a persistent file timestamp. If timestamps of files has changed, e.g. after a migration to a new server, run this script to fix the issues',
	'admin:scraper:timestamp_images:updated' => 'Timestamp change detected in %s files. URLs have been updated',

	'scraper:settings:preview_type' => 'Default Preview Type',
	'scraper:settings:preview_type:card' => 'URL card',
	'scraper:settings:preview_type:player' => 'Player',
];