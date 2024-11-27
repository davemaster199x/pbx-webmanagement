<?php namespace extension_dialplan;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of extension dialplan.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'client_access', 'dbh', 'verify_hash', 'audit_log' ] );

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

	// Get client ID access
		$client_ids = implode( ',', client_access( $user_id ));

	// Get extension(s)
		if ( empty( $params['extension_id'] )) {
		// All extensions dialplan

// 			$extension_query = <<<SQL
//   SELECT extension.*,
//          client_rpoint.rpoint_id, client_rpoint.name AS rpoint_name,
//          client.client_id, client.name AS client_name
//     FROM extension
//            INNER JOIN client_rpoint
//                    ON extension.rpoint_id = client_rpoint.rpoint_id
//            INNER JOIN client
//                    ON client_rpoint.client_id = client.client_id
//    WHERE client.client_id IN ( $client_ids )
// SQL;
// 			$extension_stmt = dbh()->prepare( $extension_query );

// 			$extension_stmt->execute();

// 			$extension = $extension_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified extension dialplan details

			$extension_dialplan_query = <<<SQL
  SELECT extension_dialplan.*
	FROM extension_dialplan
   WHERE extension_id = :extension_id
   ORDER BY extension_dialplan.prio
SQL;
			$extension_dialplan_stmt = dbh()->prepare( $extension_dialplan_query );

			$extension_dialplan_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

			$extension_dialplan_stmt->execute();

			$extension_dialplan = $extension_dialplan_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'extensions_dialplan', [ 'extensions_dialplan' => $extension_dialplan ] );
	}

?>
