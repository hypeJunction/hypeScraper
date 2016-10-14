CREATE TABLE IF NOT EXISTS `prefix_scraper_data` (
	`hash` CHAR(40) NOT NULL,
	`url` text NOT NULL,
	`data` mediumblob NOT NULL,
	UNIQUE KEY (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;