<?php

	function dbh() {
	/**
	 * Establishes a connection to the database and returns the PDO object, or
	 * returns the already opened object if it exists, or returns FALSE if
	 ( unable to establish a connection.
	 *
	 * @return PDO - The open database connection.
	 */

	// Initialize $pdo to null the first time this function is being called
		static $pdo = null;
		static $err = 0;

		if ( $pdo === null ) {
		// This is the first call to this function, establish a new connection
            // require( "{$_SERVER['DOCUMENT_ROOT']}/../config.php" );
			require( \env::$paths['methods'] . '/../config.php' );

			if ( empty( $config_server['db']['port'] )) {
			// No port is set in config_server, set a default value

				$config_server['db']['port'] = 3306;
			}

			try {
			// Establish a new connection
				$pdo = new PDO(
					"mysql:" .
					"host={$config_server['db']['host']};" .
					"port={$config_server['db']['port']};" .
					"dbname={$config_server['db']['name']};chartset=UTF8",
					$config_server['db']['user'],
					$config_server['db']['pass'],
					[ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
				);
			} catch( PDOException $e ) {
			// Couldn't establish a connection

				error_log( "DBH: Can't establish connection: " . print_r( $e, TRUE ));

				return FALSE;
			}
		} elseif ( $pdo instanceof PDO ) {
        // Check if the connection is still valid

			try {
				@$pdo->query( 'SELECT 1' );
			} catch ( PDOException $e ) {
				if ( $e->errorInfo[2] == 'MySQL server has gone away' && $err < 2 ) {
				// Increment error counter to preventive infinite recursion
					++$err;

				// Sleep for half a second to see if the MySQL server comes back
					usleep( 500000 );

				// Re-establish the broken connection
					$pdo = dbh();
				} else {
				// Couldn't re-establish connection

					error_log( "DBH: Can't re-establish connection: " . print_r( $e, TRUE ));

					return FALSE;
				}
			}
		}

		return $pdo;
	}

?>
