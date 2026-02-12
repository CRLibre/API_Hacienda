--
ALTER TABLE `files` CHANGE `md5` `md5` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL;
ALTER TABLE `users` CHANGE `settings` `settings` text COLLATE utf8mb4_unicode_ci COMMENT 'Any and all settings you would like to set';
