CREATE TABLE `voicemail` (
  `voicemail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `client_id` int(10) unsigned DEFAULT NULL,
  `mailbox` varchar(16) DEFAULT NULL,
  `password` varchar(16) DEFAULT NULL,
  `name` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `options` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`voicemail_id`),
  UNIQUE KEY `client_mailbox` (`client_id`,`mailbox`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `mailbox` (`mailbox`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `voicemail_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

