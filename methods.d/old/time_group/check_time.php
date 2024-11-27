<?php namespace time_group;

	function check_time( $params ) {
	/*
	 * Check Time
	 * Checks to see if a condition matches for the group.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

	// Get numerical day of week
		$day = date( 'N' );

		if ( $day == 7 ) $day = 0;

	// Get current number of seconds from midnight
		$minutes = ( date( 'H' ) * 60 ) + date( 'i' );

		$entry_query = <<<SQL
 SELECT time_group.time_group_id
   FROM time_group
          INNER JOIN time_condition
                  ON time_group.time_group_id = time_condition.time_group_id
  WHERE $day     BETWEEN time_condition.day_start  AND time_condition.day_end
    AND $minutes BETWEEN time_condition.time_start AND time_condition.time_end
    AND time_group.time_group_id = :time_group_id
SQL;
error_log( $entry_query );
		$entry_result = $pdo->prepare( $entry_query );
		$entry_result->execute( array(
			':time_group_id' => $params['time_group_id']
		));

		if ( $entry_result->rowCount() ) {

			return _build_response( COMPLETE, 'entry', TRUE );
		} else {

			return _build_response( COMPLETE, 'entry', FALSE );
		}
	}

?>
