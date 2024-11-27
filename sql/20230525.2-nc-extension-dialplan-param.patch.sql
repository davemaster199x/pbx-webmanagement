CREATE TABLE `extension_dialplan_param` (
  `param_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dialplan_id` int(10) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `param` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`param_id`),
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `dialplan_id` (`dialplan_id`),
  KEY `order` (`order`),
  CONSTRAINT `extension_dialplan_param_ibfk_1` FOREIGN KEY (`dialplan_id`) REFERENCES `extension_dialplan` (`dialplan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

