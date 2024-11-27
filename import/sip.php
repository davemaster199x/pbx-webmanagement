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

// 1) Break apart by individual peer entries
	$sip = [];

	foreach ( explode( "\n\n", $conf ) as $peer ) {

	// Loop through individual lines
		foreach ( explode( "\n", $peer ) as $line ) {

		// Get the first character of the line to see what it is
			switch ( substr( $line, 0, 1 )) {

				case '[' :
				// Defines the peer name

					$name = preg_replace( '/[^A-Za-z0-9]/', '', $line );

					$sip[ $name ] = [];

				break;

				case ';' :
				// Comment, we can ignore this

				break;

				default  :
				// Anything else, it's a key=value parameter

					if ( strpos( $line, '=' ) !== FALSE ) {

						list( $key, $value ) = explode( '=', $line );

						$sip[ $name ][ $key ] = $value;
					}

				break;
			}
		}
	}

// 2) Save peers to the database
	$endpoint_query = <<<SQL
  INSERT INTO endpoint
     SET rpoint_id    = :rpoint_id,
         device_type_id = :device_type_id,
         label          = :label,
         name           = :name,
         password       = :password,
         context        = :context,
         transport      = :transport,
         callerid       = :callerid,
         mailboxes      = :mailboxes
SQL;
	$endpoint_stmt = $pdo->prepare( $endpoint_query );

	$dupe_query = <<<SQL
  SELECT endpoint_id
    FROM endpoint
   WHERE name = :name
SQL;
	$dupe_stmt = $pdo->prepare( $dupe_query );

	foreach ( $sip as $name => $params ) {

		$dupe_stmt->bindParam( ':name', $name, PDO::PARAM_STR );

		$dupe_stmt->execute();

		if ( $dupe_stmt->rowCount() ) {

			echo "Duplicate entry for $name, skipping.\n";
		} else {

			echo "Adding endpoint $name... ";

			$params['transport'] = 'transport-' . $params['transport'];

			$label = str_replace( '"', '', $params['callerid'] );

			$endpoint_stmt->bindParam( ':name',           $name,                PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':label',          $label,               PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':rpoint_id',      $rpoint_id,           PDO::PARAM_INT );
			$endpoint_stmt->bindValue( ':device_type_id', NULL,                 PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':password',       $params['secret'],    PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':context',        $params['context'],   PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':transport',      $params['transport'], PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':callerid',       $params['callerid'],  PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':mailboxes',      $params['mailbox'],   PDO::PARAM_STR );

			$endpoint_stmt->execute();

			echo "done!\n";
		}
	}

//print_r( $sip );
/*
[000b82d2fb15]
; 000b82d2fb15
type=friend
secret=p4xULt336w4ffddThSnP
context=heritage-ext-1118
callerid="M_1118" <1118>
disallow=all
allow=ulaw
host=dynamic
nat=force_rport,comedia
qualify=yes
dtmfmode=rfc2833
mailbox=@heritage-voicemail
canreinvite=no
transport=udp
subscribecontext=heritage-hints
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
