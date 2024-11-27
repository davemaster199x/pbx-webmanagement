<?php namespace speeddial;

	function save( $params ) {
	/*
	 * Save
	 * Save a speeddial entry.
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

		if ( empty( $params['speeddial_id'] )) {
		// Save new speeddial
			$speeddial_query = <<<SQL
  INSERT INTO speeddial
     SET client_id  = :client_id,
         shortcut   = :shortcut,
         dest       = :dest
SQL;
			$speeddial_result = $pdo->prepare( $speeddial_query );
			$speeddial_result->execute( array(
				':client_id'  => $params['client_id'],
				':shortcut'   => $params['shortcut'],
				':dest'       => $params['dest']
			));

			return _build_response( COMPLETE, 'speeddial_saved' );
		} else {
		// Update existing speeddial
			$speeddial_query = <<<SQL
  UPDATE speeddial
     SET shortcut     = :shortcut,
         dest         = :dest
   WHERE speeddial_id = :speeddial_id
SQL;
			$speeddial_result = $pdo->prepare( $speeddial_query );
			$speeddial_result->execute( array(
				':shortcut'     => $params['shortcut'],
				':dest'         => $params['dest'],
				':speeddial_id' => $params['speeddial_id']
			));

			return _build_response( COMPLETE, 'speeddial_saved' );
		}
	}

?>
