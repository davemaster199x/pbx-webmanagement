<?php namespace api;

	function cid( $params ) {
	/*
	 * CID
	 * Retrieve a Caller ID based on passed device
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'client_access', 'dbh', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Get CallerID based on device
		$cid_query = <<<SQL
  SELECT client_location.callerid
    FROM client_location
           INNER JOIN endpoint
                   ON client_location.location_id = endpoint.location_id
   WHERE endpoint.name = :device
SQL;
		$cid_stmt = dbh()->prepare( $cid_query );

		$cid_stmt->bindParam( ':device', $params['device'], \PDO::PARAM_STR );

		$cid_stmt->execute();

		$cid_row = $cid_stmt->fetch( \PDO::FETCH_ASSOC );

		if ( !empty( $cid_row['callerid'] )) {

			return build_result( TRUE, 'callerid', [ 'callerid' => $cid_row['callerid'] ] );
		} else {

			return build_result( FALSE, 'callerid', [ 'error' => 'not_found' ] );
		}
	}

?>
