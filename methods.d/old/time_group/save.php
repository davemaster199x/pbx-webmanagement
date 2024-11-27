<?php namespace time_group;

	function save( $params ) {
	/*
	 * Save
	 * Save a time group.
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

		if ( empty( $params['time_group_id'] )) {
		// Save new time_group
			$time_group_query = <<<SQL
  INSERT INTO time_group
     SET client_id = :client_id,
         name      = :name
SQL;
			$time_group_result = $pdo->prepare( $time_group_query );
			$time_group_result->execute( array(
				':client_id' => $params['client_id'],
				':name'      => $params['name']
			));

			return _build_response( COMPLETE, 'time_group_saved' );
		} else {
		// Update existing time_group
			$time_group_query = <<<SQL
  UPDATE time_group
     SET name          = :name
   WHERE time_group_id = :time_group_id
SQL;
			$time_group_result = $pdo->prepare( $time_group_query );
			$time_group_result->execute( array(
				':name'         => $params['name'],
				':time_group_id' => $params['time_group_id']
			));

			return _build_response( COMPLETE, 'time_group_saved' );
		}
	}

?>
