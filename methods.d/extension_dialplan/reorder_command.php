<?php namespace extension_dialplan;

	function reorder_command( $params ) {
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
    
    // First swap order
		$reoder_query1 = <<<SQL
  UPDATE extension_dialplan
     SET prio        = :prio
   WHERE dialplan_id = :dialplan_id
SQL;
        $reoder1_stmt = dbh()->prepare( $reoder_query1 );

        $reoder1_stmt->bindParam( ':prio',         $params['currentOrder'],  \PDO::PARAM_INT );

        $reoder1_stmt->bindParam( ':dialplan_id',  $params['swapCommandId'], \PDO::PARAM_INT );

        $reoder1_stmt->execute();

    // Second swap order
        $reoder_query2 = <<<SQL
  UPDATE extension_dialplan
     SET prio        = :prio
   WHERE dialplan_id = :dialplan_id
SQL;
        $reoder2_stmt = dbh()->prepare( $reoder_query2 );

        $reoder2_stmt->bindParam( ':prio',         $params['swapCommandOrder'],  \PDO::PARAM_INT );

        $reoder2_stmt->bindParam( ':dialplan_id',  $params['currentId'], \PDO::PARAM_INT );

        $reoder2_stmt->execute();

		return build_result( TRUE, 'swap_command', [ 'swap_command' => $params['dialplan_id'] ] );
	}

?>
