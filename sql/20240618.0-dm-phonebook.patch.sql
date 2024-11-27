CREATE TABLE `phonebook` (
  `phonebook_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NULL DEFAULT current_timestamp(),
  `updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `client_id` int(10) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`phonebook_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `phonebook_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `phonebook_entry` (
  `entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phonebook_id` int(10) unsigned NOT NULL,
  `first_name` varchar(64) DEFAULT NULL,
  `last_name` varchar(64) DEFAULT NULL,
  `number` varchar(32) DEFAULT NULL,
  `type` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`entry_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `phonebook_id` (`phonebook_id`),
  CONSTRAINT `phonebook_entry_ibfk_1` FOREIGN KEY (`phonebook_id`) REFERENCES `phonebook` (`phonebook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;