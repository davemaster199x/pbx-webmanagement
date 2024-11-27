<?php namespace phonebook;

	function save_entry( $params ) {
	/*
	 * Save
	 * Save a phonebook entry.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

		if ( empty( $params['entry_id'] )) {
		// Save entry

			$pdo = dbh();

			$query = <<<SQL
  INSERT INTO phonebook_entry
     SET first_name   = :first_name,
         last_name    = :last_name,
         number       = :number,
         type         = :type,
         phonebook_id = :phonebook_id
SQL;
			$stmt = $pdo->prepare( $query );

			$stmt->bindParam( ':first_name',    $params['first_name'],   \PDO::PARAM_STR );
			$stmt->bindParam( ':last_name',     $params['last_name'],    \PDO::PARAM_STR );
			$stmt->bindParam( ':number',        $params['number'],       \PDO::PARAM_STR );
			$stmt->bindParam( ':type',          $params['type'],         \PDO::PARAM_STR );
            $stmt->bindParam( ':phonebook_id',  $params['phonebook_id'], \PDO::PARAM_INT );
			$stmt->execute();

			$entry_id = $pdo->lastInsertId();

			return build_result( TRUE, 'entry_saved', [ 'entry_id' => $entry_id ] );
		} else {
		// Update existing entry

			$pdo = dbh();

			$query = <<<SQL
  UPDATE phonebook_entry
     SET first_name   = :first_name,
         last_name    = :last_name,
         number       = :number,
         type         = :type
   WHERE entry_id     = :entry_id
SQL;
			$stmt = $pdo->prepare( $query );

			$stmt->bindParam( ':first_name',    $params['first_name'],   \PDO::PARAM_STR );
			$stmt->bindParam( ':last_name',     $params['last_name'],    \PDO::PARAM_STR );
			$stmt->bindParam( ':number',        $params['number'],       \PDO::PARAM_STR );
			$stmt->bindParam( ':type',          $params['type'],         \PDO::PARAM_STR );
            $stmt->bindParam( ':entry_id',      $params['entry_id'],     \PDO::PARAM_INT );
			$stmt->execute();

			return build_result( TRUE, 'entry_saved', [ 'entry_id' => $params['entry_id'] ] );
		}
	}

?>
