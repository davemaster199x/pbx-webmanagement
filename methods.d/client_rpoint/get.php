<?php namespace client_rpoint;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of endpoint.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

			$client_rpoint_query = <<<SQL
  SELECT client_rpoint.*
    FROM client_rpoint
   WHERE client_id = :client_id
   ORDER BY client_rpoint.name
SQL;
			$client_rpoint_stmt = dbh()->prepare( $client_rpoint_query );
            $client_rpoint_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			$client_rpoint_stmt->execute();

			$client_rpoint = $client_rpoint_stmt->fetchAll( \PDO::FETCH_ASSOC );

		return build_result( TRUE, 'client_rpoint', [ 'client_rpoint' => $client_rpoint ] );
	}

?>
