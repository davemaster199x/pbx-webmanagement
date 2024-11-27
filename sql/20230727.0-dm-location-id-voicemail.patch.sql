-- Update the table and add the new foreign key constraint
ALTER TABLE `voicemail`
DROP FOREIGN KEY `voicemail_ibfk_1`,
CHANGE COLUMN `client_id` `location_id` INT UNSIGNED NULL DEFAULT NULL,
ADD CONSTRAINT `voicemail_location_fk`
FOREIGN KEY (`location_id`)
REFERENCES `client_location` (`location_id`)
ON DELETE RESTRICT
ON UPDATE RESTRICT;