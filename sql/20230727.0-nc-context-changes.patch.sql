ALTER TABLE context DROP CONSTRAINT context_ibfk_2;
ALTER TABLE context DROP parent_id;

CREATE TABLE `xref_context_context` (
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(10) unsigned NOT NULL,
  `child_id` int(10) unsigned NOT NULL,
  KEY `created` (`created`),
  KEY `updated` (`updated`),
  KEY `parent_id` (`parent_id`),
  KEY `child_id` (`child_id`),
  CONSTRAINT `xref_context_context_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `context` (`context_id`),
  CONSTRAINT `xref_context_context_ibfk_2` FOREIGN KEY (`child_id`) REFERENCES `context` (`context_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

