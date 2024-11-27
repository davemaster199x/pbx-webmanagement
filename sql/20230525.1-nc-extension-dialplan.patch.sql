CREATE TABLE `extension_dialplan` (
  `dialplan_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `extension_id` int(10) unsigned NOT NULL,
  `prio` smallint(5) unsigned NOT NULL,
  `cmd` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`dialplan_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `extension_id` (`extension_id`),
  KEY `prio` (`prio`),
  CONSTRAINT `extension_dialplan_ibfk_1` FOREIGN KEY (`extension_id`) REFERENCES `extension` (`extension_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

