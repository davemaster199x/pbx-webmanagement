<?php namespace location;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of context.
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

	// Get contexts
		if ( empty( $params['location_id'] )) {
		// Get all location

			$location_query = <<<SQL
  SELECT client_location.*, client.name As cname, client.client_id
    FROM client_location
			INNER JOIN client
				ON client_location.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
SQL;

			if ( !empty( $params['client_id'] )) {

				$location_query .= ' AND client.client_id = :client_id';
			}

			$location_query = dbh()->prepare( $location_query );

			if ( !empty( $params['client_id'] )) {
			
				$location_query->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			}

			$location_query->execute();

			$location = $location_query->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified location details

			$location_query = <<<SQL
  SELECT client_location.*, client.name As cname, client.client_id
    FROM client_location
			INNER JOIN client
				ON client_location.client_id = client.client_id
	WHERE client_location.location_id = :location_id
     AND client.client_id IN ( $client_ids )
SQL;
			$location_stmt = dbh()->prepare( $location_query );

			$location_stmt->bindParam( ':location_id', $params['location_id'], \PDO::PARAM_INT );

			$location_stmt->execute();

			$location = $location_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'location', [ 'location' => $location ] );
	}

?>
