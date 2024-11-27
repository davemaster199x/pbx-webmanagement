<?php namespace routing;

	function save( $params ) {
	/*
	 * Save
	 * Save a route.
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

		if ( empty( $params['routing_id'] )) {
		// Save new route
			$routing_query = <<<SQL
  INSERT INTO routing
     SET client_id = :client_id,
         status    = 1,
         dest      = :dest
SQL;
			$routing_result = $pdo->prepare( $routing_query );
			$routing_result->execute( array(
				':client_id' => $params['client_id'],
				':dest'      => $params['dest']
			));

			return _build_response( COMPLETE, 'routing_saved' );
		} else {
		// Update existing client
			$routing_query = <<<SQL
  UPDATE routing
     SET dest       = :dest
   WHERE routing_id = :routing_id
SQL;
			$routing_result = $pdo->prepare( $routing_query );
			$routing_result->execute( array(
				':dest'       => $params['dest'],
				':routing_id' => $params['routing_id']
			));

			return _build_response( COMPLETE, 'routing_saved' );
		}
	}

?>
