CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seat` tinyint(1) unsigned NOT NULL,
  `tier` tinyint(1) unsigned NOT NULL,
  `section` tinyint(1) unsigned NOT NULL,
  `status` enum('available','occupied') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `section` (`section`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;