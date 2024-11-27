CREATE TABLE `context` (
  `context_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `location_id` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `context` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`context_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `location_id` (`location_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `context_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `client_location` (`location_id`),
  CONSTRAINT `context_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `context` (`context_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

