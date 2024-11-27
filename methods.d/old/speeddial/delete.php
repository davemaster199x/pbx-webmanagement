<?php namespace speeddial;

	function delete( $params ) {
	/*
	 * Delete
	 * Delete a speeddial entry.
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

	// Save new speeddial entry
		$speeddial_query = <<<SQL
  DELETE
    FROM speeddial
   WHERE client_id    = :client_id
     AND speeddial_id = :speeddial_id
SQL;
		$speeddial_result = $pdo->prepare( $speeddial_query );
		$speeddial_result->execute( array(
			':client_id'    => $params['client_id'],
			':speeddial_id' => $params['speeddial_id']
		));

error_log( print_r( $speeddial_result->errorInfo(), TRUE ));
error_log( print_r( $params, TRUE ));
		return _build_response( COMPLETE, 'speeddial_deleted' );
	}

?>
