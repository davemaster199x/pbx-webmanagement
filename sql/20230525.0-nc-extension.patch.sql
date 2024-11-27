CREATE TABLE `extension` (
  `extension_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location_id` int(10) unsigned NOT NULL,
  `ext` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `extension_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `client_location` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

