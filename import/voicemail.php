<?php

// Include config
	require( __DIR__ . '/../config.php' );

// Read input
	if ( isset( $argv[1] )) {

		if ( file_exists( $argv[1] )) {

			$conf = file_get_contents( $argv[1] );
		} else {

			usage( "Can't find file {$argv[1]}." );
		}
	} else {

		usage( "Missing filename parameter." );
	}

	if ( isset( $argv[2] )) {

		if ( (int) $argv[2] == $argv[2] ) {

			$rpoint_id = $argv[2];
		} else {

			usage( "Invalid value for rpoint_id." );
		}
	} else {

		usage( "Missing filename parameter." );
	}

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

// Parse import file

// 1) Break apart by individual mailboxes
	$voicemail = [];

	foreach ( explode( "\n", $conf ) as $line ) {

		list( $mailbox, $others ) = explode( '=>', $line );

		if ( !empty( $mailbox )) {

			$parts = explode( ',', $others );

			$voicemail[ trim( $mailbox ) ] = [
				'password' => trim( $parts[0] ),
				'name'     => trim( $parts[1] ),
				'email'    => trim( $parts[2] ),
				'options'  => trim( $parts[4] )
			];
		}
	}

// 2) Save mailboxes to the database
	$endpoint_query = <<<SQL
  INSERT INTO voicemail
     SET rpoint_id = :rpoint_id,
         mailbox   = :mailbox,
         password  = :password,
         name      = :name,
         email     = :email,
         options   = :options
SQL;
	$endpoint_stmt = $pdo->prepare( $endpoint_query );

	$dupe_query = <<<SQL
  SELECT voicemail_id
    FROM voicemail
   WHERE mailbox   = :mailbox
     AND rpoint_id = :rpoint_id
SQL;
	$dupe_stmt = $pdo->prepare( $dupe_query );

	foreach ( $voicemail as $mailbox => $params ) {

		$dupe_stmt->bindParam( ':mailbox',   $mailbox,   PDO::PARAM_STR );
		$dupe_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );

		$dupe_stmt->execute();

		if ( $dupe_stmt->rowCount() ) {

			echo "Duplicate entry for $mailbox, skipping.\n";
		} else {

			echo "Adding endpoint $mailbox... ";

			$endpoint_stmt->bindParam( ':rpoint_id', $rpoint_id,          PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':mailbox',   $mailbox,            PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':password',  $params['password'], PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':name',      $params['name'],     PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':email',     $params['email'],    PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':options',   $params['options'],  PDO::PARAM_STR );

			$endpoint_stmt->execute();

			echo "done!\n";
		}
	}

//print_r( $voicemail );
/*
451 => 1234,Robert Brown,bob.brow@heritageacademyaz.com,,delete=yes
*/

	function usage( $message, $exit = 1 ) {
	/**
	 * Print message, then script usage, and optionally exit.
	 *
	 * @param message string - The message to print before showing the usage.
	 * @param exit    int    - A zero value won't exit, a non-zero value will exit with exitcode $exit.
	 *
	 * @return void
	 */

		echo "\n$message\n\n";

		$file = './' . basename( __FILE__ );

		echo <<<TEXT
Usage: $file [config] [rpoint_id]

TEXT;

		if ( $exit ) {

			exit ( $exit );
		}
	}

?>
