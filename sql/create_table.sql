CREATE TABLE `prefix_url_meta_cache` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hash` varchar(255) DEFAULT NULL,
  `long_url` text NOT NULL,
  `meta` text NOT NULL,
  `time_created` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;