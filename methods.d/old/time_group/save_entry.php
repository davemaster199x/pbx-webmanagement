<?php namespace time_group;

	function save_entry( $params ) {
	/*
	 * Save Entry
	 * Save a time_group entry.
	 */

error_log( print_r( $params, TRUE ));
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

		$time = explode( ':', $params['time_start'] );

		$time_start = ( 60 * $time[0] ) + $time[1];

		$time = explode( ':', $params['time_end'] );

		$time_end = ( 60 * $time[0] ) + $time[1];

		if ( empty( $params['time_condition_id'] )) {
		// Save new time_group entry
			$entry_query = <<<SQL
  INSERT INTO time_condition
     SET time_group_id = :time_group_id,
         day_start     = :day_start,
         day_end       = :day_end,
         time_start    = :time_start,
         time_end      = :time_end
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':time_group_id' => $params['time_group_id'],
				':day_start'     => $params['day_start'],
				':day_end'       => $params['day_end'],
				':time_start'    => $time_start,
				':time_end'      => $time_end
			));

			return _build_response( COMPLETE, 'entry_saved' );
		} else {
		// Update existing time_group entry
			$entry_query = <<<SQL
  UPDATE time_condition
     SET day_start         = :day_start,
         day_end           = :day_end,
         time_start        = :time_start,
         time_end          = :time_end
   WHERE time_group_id     = :time_group_id
     AND time_condition_id = :time_condition_id
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':day_start'         => $params['day_start'],
				':day_end'           => $params['day_end'],
				':time_start'        => $time_start,
				':time_end'          => $time_end,
				':time_group_id'     => $params['time_group_id'],
				':time_condition_id' => $params['time_condition_id']
			));

			return _build_response( COMPLETE, 'entry_saved' );
		}
	}

?>
