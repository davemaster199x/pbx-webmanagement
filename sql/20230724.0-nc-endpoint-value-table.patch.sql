CREATE TABLE `endpoint_value` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `endpoint_id` int(10) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`value_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `endpoint_id` (`endpoint_id`),
  KEY `name` (`name`),
  CONSTRAINT `endpoint_value_ibfk_1` FOREIGN KEY (`endpoint_id`) REFERENCES `endpoint` (`endpoint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

