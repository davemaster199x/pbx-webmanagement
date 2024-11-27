<?php namespace phonebook;

	function save( $params ) {
	/*
	 * Save
	 * Save a phonebook.
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

		if ( empty( $params['phonebook_id'] )) {
		// Save phonebook

			$pdo = dbh();

			$phonebook_query = <<<SQL
  INSERT INTO phonebook
     SET client_id = :client_id,
         name      = :name
SQL;
			$phonebook_stmt = $pdo->prepare( $phonebook_query );

			$phonebook_stmt->bindParam( ':client_id',   $params['client_id'],   \PDO::PARAM_INT );
			$phonebook_stmt->bindParam( ':name',        $params['name'],        \PDO::PARAM_STR );

			$phonebook_stmt->execute();

			$phonebook_stmt_id = $pdo->lastInsertId();

			return build_result( TRUE, 'phonebook_saved', [ 'phonebook_id' => $phonebook_stmt_id ] );
		} else {
		// Update existing phonebook

			$pdo = dbh();

			$phonebook_query = <<<SQL
  UPDATE phonebook
     SET client_id    = :client_id,
         name         = :name
   WHERE phonebook_id = :phonebook_id
SQL;
			$phonebook_stmt = $pdo->prepare( $phonebook_query );

			$phonebook_stmt->bindParam( ':client_id',    $params['client_id'],    \PDO::PARAM_INT );
			$phonebook_stmt->bindParam( ':name',         $params['name'],         \PDO::PARAM_STR );
			$phonebook_stmt->bindParam( ':phonebook_id', $params['phonebook_id'], \PDO::PARAM_INT );

			$phonebook_stmt->execute();

			return build_result( TRUE, 'phonebook_saved', [ 'phonebook_id' => $params['phonebook_id'] ] );
		}
	}

?>
