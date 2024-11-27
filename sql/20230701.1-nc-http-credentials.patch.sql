ALTER TABLE client ADD http_username VARCHAR(64) DEFAULT NULL AFTER name;
ALTER TABLE client ADD http_password VARCHAR(64) DEFAULT NULL AFTER http_username;

