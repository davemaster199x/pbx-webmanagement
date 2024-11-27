<?php namespace time_group;

	function get_entries( $params ) {
	/*
	 * Get Entries
	 * Retrieve a list of time conditions.
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

		if ( isset( $params['client_id'] ) && isset( $params['time_group_id'] )) {
		// Get all time_groups for client
			$entry_query = <<<SQL
	  SELECT time_condition.*
		FROM time_condition
               INNER JOIN time_group
                       ON time_condition.time_group_id = time_group.time_group_id
       WHERE time_group.client_id         = :client_id
         AND time_condition.time_group_id = :time_group_id
	   ORDER BY time_condition.day_start, time_condition.time_start
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':client_id'     => $params['client_id'],
				':time_group_id' => $params['time_group_id']
			));

			$entries = $entry_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['time_group_id'] ) && isset( $params['time_condition_id'] )) {
		// Get specified route
			$entry_query = <<<SQL
	  SELECT time_condition.*
		FROM time_condition
       WHERE time_condition.time_group_id     = :time_group_id
         AND time_condition.time_condition_id = :time_condition_id
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':time_group_id'     => $params['time_group_id'],
				':time_condition_id' => $params['time_condition_id']
			));

			$entries = $entry_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_and_time_group_or_time_group_and_time_condition' );
		}

		$entries_adjusted = array();

		foreach ( $entries as $entry ) {
			$entry['time_start'] = intval( $entry['time_start'] / 60 ) . ':' . str_pad( $entry['time_start'] % 60, 2, '0', STR_PAD_LEFT );
			$entry['time_end']   = intval( $entry['time_end'] / 60 ) . ':' . str_pad( $entry['time_end'] % 60, 2, '0', STR_PAD_LEFT );

			$entries_adjusted[] = $entry;
		}

		return _build_response( COMPLETE, 'entry', array( 'entry' => $entries_adjusted ));
	}

?>
