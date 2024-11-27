<?php namespace endpoint;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of endpoint.
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

	// Get endpoints
		if ( empty( $params['endpoint_id'] )) {
		// Get all endpoints

			$endpoint_query = <<<SQL
  SELECT endpoint.endpoint_id, endpoint.rpoint_id, endpoint.label, endpoint.name AS endpoint_name, endpoint.context, endpoint.transport, endpoint.callerid, endpoint.mailboxes,
         client_rpoint.name AS client_rpoint_name,
         client_location.name AS client_location,
         device_type.name AS device_type_name,
         client.client_id, client.name AS client_name
    FROM endpoint
            LEFT JOIN client_rpoint
                   ON endpoint.rpoint_id = client_rpoint.rpoint_id
            LEFT JOIN client_location
                   ON endpoint.location_id = client_location.location_id
            LEFT JOIN device_type
                   ON endpoint.device_type_id = device_type.type_id
            LEFT JOIN client
                   ON client_rpoint.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
SQL;

			if ( !empty( $params['client_id'] )) {

				$endpoint_query .= ' AND client.client_id = :client_id';
			}

			if ( !empty( $params['rpoint_id'] )) {

				$endpoint_query .= ' AND endpoint.rpoint_id = :rpoint_id';
			}

			$endpoint_query .= ' ORDER BY client_name, client_location, client_rpoint_name, endpoint.label, endpoint.name';

			$endpoint_stmt = dbh()->prepare( $endpoint_query );

			if ( !empty( $params['client_id'] )) {
			
				$endpoint_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			}

			if ( !empty( $params['rpoint_id'] )) {

				$endpoint_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );
			}


			$endpoint_stmt->execute();

			$endpoint = $endpoint_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified endpoint details

			$endpoint_query = <<<SQL
  SELECT endpoint.*,
         client_rpoint.client_id
	FROM endpoint
            LEFT JOIN client_rpoint
                   ON endpoint.rpoint_id = client_rpoint.rpoint_id
            LEFT JOIN client_location
                   ON endpoint.location_id = client_location.location_id
   WHERE endpoint.endpoint_id = :endpoint_id
     AND client_rpoint.client_id IN ( $client_ids )
ORDER BY endpoint.label
SQL;
			$endpoint_stmt = dbh()->prepare( $endpoint_query );

			$endpoint_stmt->bindParam( ':endpoint_id', $params['endpoint_id'], \PDO::PARAM_INT );

			$endpoint_stmt->execute();

			$endpoint = $endpoint_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'endpoint', [ 'endpoint' => $endpoint ] );
	}

?>
