<?php namespace inbound;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of routes.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );
		if ( !$user_id ) {
			return _build_response( ERROR, 'invalid_hash' );
		}

		if ( isset( $params['client_id'] )) {
		// Get all routes for client
			$inbound_query = <<<SQL
	  SELECT inbound.inbound_id, inbound.did,
             routing.dest
		FROM inbound
               INNER JOIN routing
                       ON inbound.routing_id = routing.routing_id
       WHERE inbound.client_id = :client_id
	   ORDER BY inbound.did
SQL;
			$inbound_result = $pdo->prepare( $inbound_query );
			$inbound_result->execute( array(
				':client_id' => $params['client_id']
			));

			$inbound = $inbound_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['inbound_id'] )) {
		// Get specified route
			$inbound_query = <<<SQL
	  SELECT inbound.*
		FROM inbound
               INNER JOIN routing
                       ON inbound.routing_id = routing.routing_id
       WHERE inbound.inbound_id = :inbound_id
SQL;
			$inbound_result = $pdo->prepare( $inbound_query );
			$inbound_result->execute( array(
				':inbound_id' => $params['inbound_id']
			));

			$inbound = $inbound_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_route' );
		}

		return _build_response( COMPLETE, 'inbound', array( 'inbound' => $inbound ));
	}

?>
