CREATE TABLE `client_location` (
  `location_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `callerid` varchar(64) DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`location_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE endpoint ADD location_id INT UNSIGNED DEFAULT NULL AFTER device_type_id;

