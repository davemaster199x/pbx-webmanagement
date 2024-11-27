RENAME TABLE client_location TO client_rpoint;
ALTER TABLE client_rpoint CHANGE location_id rpoint_id INT UNSIGNED AUTO_INCREMENT;
ALTER TABLE context CHANGE location_id rpoint_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE endpoint CHANGE location_id rpoint_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE extension CHANGE location_id rpoint_id INT UNSIGNED DEFAULT NULL;
ALTER TABLE voicemail CHANGE location_id rpoint_id INT UNSIGNED DEFAULT NULL;

