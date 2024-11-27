<?php namespace client_location;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of endpoint.
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

			$client_location_query = <<<SQL
  SELECT client_location.*
    FROM client_location
   WHERE client_id = :client_id
SQL;
			$client_location_stmt = dbh()->prepare( $client_location_query );
            $client_location_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			$client_location_stmt->execute();

			$client_location = $client_location_stmt->fetchAll( \PDO::FETCH_ASSOC );

		return build_result( TRUE, 'client_location', [ 'client_location' => $client_location ] );
	}

?>
