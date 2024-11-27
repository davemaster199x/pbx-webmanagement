<?php namespace speeddial;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of speeddial boxes.
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
		// Get all speeddial entries for client
			$speeddial_query = <<<SQL
	  SELECT speeddial.*
		FROM speeddial
       WHERE speeddial.client_id = :client_id
	   ORDER BY speeddial.shortcut
SQL;
			$speeddial_result = $pdo->prepare( $speeddial_query );
			$speeddial_result->execute( array(
				':client_id' => $params['client_id']
			));

			$speeddial = $speeddial_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['speeddial_id'] )) {
		// Get specified route
			$speeddial_query = <<<SQL
	  SELECT speeddial.*
		FROM speeddial
       WHERE speeddial.speeddial_id = :speeddial_id
SQL;
			$speeddial_result = $pdo->prepare( $speeddial_query );
			$speeddial_result->execute( array(
				':speeddial_id' => $params['speeddial_id']
			));

			$speeddial = $speeddial_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_speeddial' );
		}

		return _build_response( COMPLETE, 'speeddial', array( 'speeddial' => $speeddial ));
	}

?>
