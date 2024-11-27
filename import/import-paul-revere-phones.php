<?php

// Include config
	require( __DIR__ . '/../config.php' );

// Read CSV
	$fp = fopen( 'configs/paul-revere-phones.csv', 'r' );

// Establish DB connection
	$pdo = new PDO(
		"mysql:" .
		"host={$config_server['db']['host']};" .
		"port={$config_server['db']['port']};" .
		"dbname={$config_server['db']['name']};chartset=UTF8",
		$config_server['db']['user'],
		$config_server['db']['pass'],
		[ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
	);

// 1) Parse CSV file
	$peers = [];

	while ( $line = fgetcsv( $fp, NULL, "\t" )) {

		list( $device_type, $a, $mac, $d, $password, $b, $room, $ext ) = $line;

		$mac = strtolower( preg_replace( '/[^0-9A-Fa-f]/', '', $mac ));

		$peers[] = [
			'name'     => $mac,
			'label'    => 'Room ' . $room,
			'password' => '!ThePants',
			'context'  => 'heritage-ext-' . $ext,
			'callerid' => $ext
		];			
	}

// 2) Save peers to the database
	$endpoint_query = <<<SQL
  INSERT INTO endpoint
     SET rpoint_id    = 8,
         device_type_id = 2,
         label          = :label,
         name           = :name,
         password       = :password,
         context        = :context,
         transport      = 'transport-tls',
         callerid       = :callerid,
         mailboxes      = ''
SQL;
	$endpoint_stmt = $pdo->prepare( $endpoint_query );

	$dupe_query = <<<SQL
  SELECT endpoint_id
    FROM endpoint
   WHERE name = :name
SQL;
	$dupe_stmt = $pdo->prepare( $dupe_query );

	foreach ( $peers as $peer ) {

		$dupe_stmt->bindParam( ':name', $name, PDO::PARAM_STR );

		$dupe_stmt->execute();

		if ( $dupe_stmt->rowCount() ) {

			echo "Duplicate entry for $name, skipping.\n";
		} else {

			echo "Adding endpoint $name... ";

			$endpoint_stmt->bindParam( ':name',     $peer['name'],     PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':label',    $peer['label'],    PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':password', $peer['password'], PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':context',  $peer['context'],  PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':callerid', $peer['callerid'], PDO::PARAM_STR );

			$endpoint_stmt->execute();

			echo "done!\n";
		}
	}

?>
