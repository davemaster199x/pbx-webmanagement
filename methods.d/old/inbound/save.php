<?php namespace inbound;

	function save( $params ) {
	/*
	 * Save
	 * Save inbound route.
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

		if ( $params['status'] == 0 ) {
			$route_query = <<<SQL
  SELECT routing_id
    FROM routing
   WHERE client_id = :client_id
     AND status    = 0
SQL;
			$route_result = $pdo->prepare( $route_query );
			$route_result->execute( array(
				':client_id' => $params['client_id']
			));

			$route_row = $route_result->fetch( \PDO::FETCH_ASSOC );

			$routing_id = $route_row['routing_id'];
		} elseif ( $params['status'] == 1 ) {
			if ( empty( $params['routing_id'] )) {
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

				$routing_id = $pdo->lastInsertId();
			} else {
			// Update existing client
				$routing_id = $params['routing_id'];
			}
		}

		$inbound_query = <<<SQL
  UPDATE inbound
     SET routing_id = :routing_id
   WHERE inbound_id = :inbound_id
SQL;
		$inbound_result = $pdo->prepare( $inbound_query );
		$inbound_result->execute( array(
			':routing_id' => $routing_id,
			':inbound_id' => $params['inbound_id']
		));

		return _build_response( COMPLETE, 'inbound_saved' );
	}

?>
