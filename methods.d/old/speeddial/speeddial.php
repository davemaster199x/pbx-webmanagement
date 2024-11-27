<?php namespace speeddial;

	function speeddial( $params ) {
	/*
	 * Speeddial
	 * Retrieve the speeddial destination of a shortcut.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

		if ( !isset( $params['client_id'] )) {
			return _build_response( ERROR, 'client_id_not_specified' );
		}

		if ( !isset( $params['shortcut'] )) {
			return _build_response( ERROR, 'shortcut_not_specified' );
		}

		$speeddial_query = <<<SQL
  SELECT speeddial.dest
	FROM speeddial
   WHERE speeddial.client_id = :client_id
     AND speeddial.shortcut  = :shortcut
SQL;
		$speeddial_result = $pdo->prepare( $speeddial_query );
		$speeddial_result->execute( array(
			':client_id' => $params['client_id'],
			':shortcut'  => $params['shortcut']
		));

		$speeddial = $speeddial_result->fetch( \PDO::FETCH_ASSOC );

		return _build_response( COMPLETE, 'speeddial', array( 'speeddial' => $speeddial ));
	}

?>
