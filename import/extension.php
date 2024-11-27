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

// 1) Break apart by individual extension entries
	$extensions = [];
	$ignored    = [];

	foreach ( explode( "\n", $conf ) as $line ) {

	// Figure out of what this line is
		if ( substr( trim( $line ), 0, 5 ) == 'exten' ) {
		// Extension definition

			if ( !isset( $context )) {
			// No context found at the beginning of the file

				echo "Missing context at the beginning of the file, exiting...\n";

				exit( 1 );
			}

		// Get the extension
			$line = substr( $line, 9 );

			list( $ext, $prio, $cmd ) = explode( ',', $line, 3 );

			$param = substr( $cmd, strpos( $cmd, '(' ) + 1 );
			$param = substr( $param, 0, strlen( $param ) - 1 );

			$cmd = substr( $cmd, 0, strpos( $cmd, '(' ));

			$ignore_cmd = FALSE;

			switch ( strtolower( $cmd )) {

				case 'answer' :
				// No parameters, create empty array

					$params = [];

				break;

				case 'background' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				case 'dial' :
				// Split all parameters by comma

					$params = explode( ',', $param );
					
				break;

				case 'goto' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				case 'hangup' :
				// No parameters, create empty array

					$params = [];

				break;

				case 'log' :

					$params = explode( ',', $param, 2 );

				break;

				case 'page' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				case 'playback' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				case 'set' :
				// Split all parameters by comma, except when enclosed in parentehses

					$param  = preg_replace( '/(\(.*),(.*\))/', '$1|^|$2', $param );
					$params = explode( ',', $param );

					$params = array_map( function( $val ) {
						return str_replace( '|^|', ',', $val );
					}, $params );

				break;

				case 'voicemail' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				case 'wait' :
				// Split all parameters by comma

					$params = explode( ',', $param );

				break;

				default :

//					$ignore_cmd = TRUE;
//					$params     = [];

//					$ignored[] = $cmd;

					$params = [ $param ];

				break;
			}

			$params = array_map( 'trim', $params );

			if ( !empty( $ext ) && !$ignore_cmd ) {
			// Add this command

				if ( empty( $extensions[ $context ]['exten'][ $ext ] )) {

					$extensions[ $context ]['exten'][ $ext ] = [];
				}

				if ( is_numeric( $prio )) {

					$extensions[ $context ]['exten'][ $ext ][ $prio ] = [
						'cmd'    => $cmd,
						'params' => $params
					];
				} else {

					$extensions[ $context ]['exten'][ $ext ][] = [
						'cmd'    => $cmd,
						'params' => $params
					];
				}
			}
		} elseif( substr( trim( $line ), 0, 1 ) == '[' && substr( trim( $line ), -1, 1 ) == ']' ) {
		// Context definition

			$context = substr( trim( $line ), 1, -1 );

			$extensions[ $context ] = [];
		} elseif ( substr( trim( $line ), 0, 7 ) == 'include' ) {
		// Process context include

			if ( !isset( $context )) {
			// No context found at the beginning of the file

				echo "Missing context at the beginning of the file, exiting...\n";

				exit( 1 );
			}

			list( $keyword, $included ) = explode( '=>', $line );

			$extensions[ $context ]['include'][] = trim( $included );
		}
	}

//print_r( $extensions );
//exit();
// 2) Save extensions and contexts to the database

// Add an extension
	$extension_query = <<<SQL
  INSERT INTO extension
     SET rpoint_id  = :rpoint_id,
         context_id = :context_id,
         ext        = :ext
SQL;
	$extension_stmt = $pdo->prepare( $extension_query );

// Check for a duplicate extension
	$extension_dupe_query = <<<SQL
  SELECT extension_id
    FROM extension
   WHERE rpoint_id  = :rpoint_id
     AND context_id = :context_id
     AND ext        = :ext
SQL;
	$extension_dupe_stmt = $pdo->prepare( $extension_dupe_query );

// Add a dialplan entry for an extension
	$dialplan_query = <<<SQL
  INSERT INTO extension_dialplan
     SET extension_id = :extension_id,
         prio         = :prio,
         cmd          = :cmd
SQL;
	$dialplan_stmt = $pdo->prepare( $dialplan_query );

// Add a parameter for a diaplan entry
	$param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id = :dialplan_id,
         `order`     = :order,
         param       = :param
SQL;
	$param_stmt = $pdo->prepare( $param_query );

// Add a context
	$context_query = <<<SQL
  INSERT INTO context
     SET rpoint_id = :rpoint_id,
         context   = :context
SQL;
	$context_stmt = $pdo->prepare( $context_query );

// Link included contexts
	$include_query = <<<SQL
  INSERT INTO xref_context_context
     SET parent_id = :parent_id,
         child_id  = :child_id
SQL;
	$include_stmt = $pdo->prepare( $include_query );

// Check for duplicate includes
	$include_dupe_query = <<<SQL
  SELECT *
    FROM xref_context_context
   WHERE parent_id = :parent_id
     AND child_id  = :child_id
SQL;
	$include_dupe_stmt = $pdo->prepare( $include_dupe_query );

	foreach ( $extensions as $context => $components ) {

		if (( $context_id = get_context_id_by_name( $rpoint_id, $context )) === FALSE ) {
		// Add context

			$context_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );
			$context_stmt->bindParam( ':context',   $context,   PDO::PARAM_STR );

			$context_stmt->execute();

			$context_id = $pdo->lastInsertId();
		}

		if ( isset( $components['include'] )) {

			foreach ( $components['include'] as $include ) {

				if (( $include_id = get_context_id_by_name( $rpoint_id, $include )) === FALSE ) {
				// Add context

					$context_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );
					$context_stmt->bindParam( ':context',   $include,   PDO::PARAM_STR );

					$context_stmt->execute();

					$include_id = $pdo->lastInsertId();
				}

			// Check for duplicate relationship
				$include_dupe_stmt->bindParam( ':parent_id', $context_id, PDO::PARAM_INT );
				$include_dupe_stmt->bindParam( ':child_id',  $include_id, PDO::PARAM_INT );

				$include_dupe_stmt->execute();

				if ( $include_dupe_stmt->rowCount() == 0 ) {

					$include_stmt->bindParam( ':parent_id', $context_id, PDO::PARAM_INT );
					$include_stmt->bindParam( ':child_id',  $include_id, PDO::PARAM_INT );

					$include_stmt->execute();
				}
			}
		}

		if ( isset( $components['exten'] )) {
				
			foreach ( $components['exten'] as $ext => $dialplan ) {

				$extension_dupe_stmt->bindParam( ':rpoint_id',  $rpoint_id,  PDO::PARAM_INT );
				$extension_dupe_stmt->bindParam( ':context_id', $context_id, PDO::PARAM_INT );
				$extension_dupe_stmt->bindParam( ':ext',        $ext,        PDO::PARAM_STR );

				$extension_dupe_stmt->execute();

				if ( $extension_dupe_stmt->rowCount() ) {

					echo "Duplicate entry for $ext, skipping.\n";
				} else {

					echo "Adding extension $ext... ";

					$extension_stmt->bindParam( ':rpoint_id',  $rpoint_id,  PDO::PARAM_INT );
					$extension_stmt->bindParam( ':context_id', $context_id, PDO::PARAM_INT );
					$extension_stmt->bindParam( ':ext',        $ext,        PDO::PARAM_STR );

					$extension_stmt->execute();

					$extension_id = $pdo->lastInsertId();

					foreach ( $dialplan as $prio => $cmds ) {

						$dialplan_stmt->bindParam( ':extension_id', $extension_id, PDO::PARAM_INT );
						$dialplan_stmt->bindValue( ':prio',         $prio,         PDO::PARAM_INT );
						$dialplan_stmt->bindParam( ':cmd',          $cmds['cmd'],  PDO::PARAM_STR );

						$dialplan_stmt->execute();

						$dialplan_id = $pdo->lastInsertId();

						foreach ( $cmds['params'] as $key => $param ) {

							$param_stmt->bindParam( ':dialplan_id', $dialplan_id, PDO::PARAM_INT );
							$param_stmt->bindParam( ':order',       $key,         PDO::PARAM_INT );
							$param_stmt->bindParam( ':param',       $param,       PDO::PARAM_STR );

							$param_stmt->execute();
						}
					}

					echo "done!\n";
				}
			}
		}
	}

	if ( !empty( $ignored )) {

		echo "The following commands were found but ignored:\n\n";

		foreach ( array_unique( $ignored ) as $cmd ) {

			echo "* $cmd\n";
		}
	}

	function get_context_id_by_name( $rpoint_id, $context ) {
	/**
	 * Returns the context_id given the name and rpoint_id.
	 *
	 * @param rpoint_id int    - The registration point associated with the context.
	 * @param context   string - The name of the context.
	 *
	 * @return int/bool - The ID of the context, or FALSE if none found.
	 */

		global $pdo;

		$context_query = <<<SQL
  SELECT context_id
    FROM context
   WHERE context   = :context
     AND rpoint_id = :rpoint_id
SQL;
		$context_stmt = $pdo->prepare( $context_query );

		$context_stmt->bindParam( ':context',   $context,   PDO::PARAM_STR );
		$context_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );

		$context_stmt->execute();

		if ( $context_stmt->rowCount() == 1 ) {

			$context_row = $context_stmt->fetch( PDO::FETCH_ASSOC );

			return $context_row['context_id'];
		} else {

			return FALSE;
		}
	}

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
