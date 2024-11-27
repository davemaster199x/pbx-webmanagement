<?php namespace routing;

	function routing( $params ) {
	/*
	 * Routing
	 * Retrieve the routing rules given a phone number.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

		if ( !isset( $params['did'] )) {
			return _build_response( ERROR, 'did_not_specified' );
		}

		$routing_query = <<<SQL
  SELECT routing.status, routing.dest
	FROM routing
           INNER JOIN inbound
                   ON routing.routing_id = inbound.routing_id
   WHERE inbound.did = :did
SQL;
		$routing_result = $pdo->prepare( $routing_query );
		$routing_result->execute( array(
			':did' => $params['did']
		));

		$routing = $routing_result->fetch( \PDO::FETCH_ASSOC );

		return _build_response( COMPLETE, 'routing', array( 'routing' => $routing ));
	}

?>
