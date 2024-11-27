ALTER TABLE extension ADD context_id INT UNSIGNED DEFAULT NULL AFTER location_id;
ALTER TABLE extension ADD CONSTRAINT FOREIGN KEY (context_id) references context(context_id);

