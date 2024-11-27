<?php namespace phonebook;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of phonebook.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'client_access', 'dbh', 'verify_hash', 'audit_log' ] );

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

    // Get client ID access
		$client_ids = implode( ',', client_access( $user_id ));

	// Get phonebook(s)
		if ( empty( $params['phonebook_id'] )) {
		// All phonebook

			$phonebook_query = <<<SQL
  SELECT phonebook.*
    FROM phonebook
   WHERE phonebook.client_id IN ( $client_ids )
ORDER BY phonebook.name
SQL;

			$phonebook_stmt = dbh()->prepare( $phonebook_query );

			$phonebook_stmt->execute();

			$phonebook = $phonebook_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified phonebook details

            $phonebook_query = <<<SQL
  SELECT phonebook.*
    FROM phonebook
   WHERE phonebook.client_id IN ( $client_ids )
     AND phonebook_id = :phonebook_id
ORDER BY phonebook.name
SQL;

			$phonebook_stmt = dbh()->prepare( $phonebook_query );

			$phonebook_stmt->bindParam( ':phonebook_id', $params['phonebook_id'], \PDO::PARAM_INT );

			$phonebook_stmt->execute();

			$phonebook = $phonebook_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'phonebook', [ 'phonebook' => $phonebook ] );
	}

?>
