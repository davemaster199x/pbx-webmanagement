<?php namespace time_group;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of time groups.
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
		// Get all time_groups for client
			$time_group_query = <<<SQL
	  SELECT time_group.*
		FROM time_group
       WHERE time_group.client_id = :client_id
	   ORDER BY time_group.name
SQL;
			$time_group_result = $pdo->prepare( $time_group_query );
			$time_group_result->execute( array(
				':client_id' => $params['client_id']
			));

			$time_group = $time_group_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['time_group_id'] )) {
		// Get specified route
			$time_group_query = <<<SQL
	  SELECT time_group.*
		FROM time_group
       WHERE time_group.time_group_id = :time_group_id
SQL;
			$time_group_result = $pdo->prepare( $time_group_query );
			$time_group_result->execute( array(
				':time_group_id' => $params['time_group_id']
			));

			$time_group = $time_group_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_time_group' );
		}

		return _build_response( COMPLETE, 'time_group', array( 'time_group' => $time_group ));
	}

?>
