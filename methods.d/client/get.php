<?php namespace client;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of clients.
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

		$client_ids = implode( ',', client_access( $user_id ));

	// Get list of accessible clients
		if ( empty( $params['client_id'] )) {
		// Get all client details

			$client_query = <<<SQL
  SELECT client.*
	FROM client
   WHERE client_id IN ( $client_ids )
   ORDER BY client.name
SQL;
			$client_stmt = dbh()->prepare( $client_query );

			$client_stmt->execute();

			$clients = $client_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified client details

			$client_query = <<<SQL
  SELECT client.*
	FROM client
   WHERE client.client_id = :client_id
     AND client_id IN ( $client_ids )
SQL;
			$client_stmt = dbh()->prepare( $client_query );

			$client_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );

			$client_stmt->execute();

			$clients = $client_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'clients', [ 'clients' => $clients ] );
	}

?>
