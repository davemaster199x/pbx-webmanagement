<?php namespace routing;

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
			$routing_query = <<<SQL
	  SELECT routing.*
		FROM routing
       WHERE routing.client_id = :client_id
	   ORDER BY routing.status, routing.dest
SQL;
			$routing_result = $pdo->prepare( $routing_query );
			$routing_result->execute( array(
				':client_id' => $params['client_id']
			));

			$routing = $routing_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['routing_id'] )) {
		// Get specified route
			$routing_query = <<<SQL
	  SELECT routing.*
		FROM routing
       WHERE routing.routing_id = :routing_id
SQL;
			$routing_result = $pdo->prepare( $routing_query );
			$routing_result->execute( array(
				':routing_id' => $params['routing_id']
			));

			$routing = $routing_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_route' );
		}

		return _build_response( COMPLETE, 'routing', array( 'routing' => $routing ));
	}

?>
