<?php namespace extension_dialplan;

	function delete_command( $params ) {
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

		$param_query = <<<SQL
  DELETE
    FROM extension_dialplan_param
   WHERE dialplan_id = :dialplan_id
SQL;
		$param_stmt = dbh()->prepare( $param_query );

		$param_stmt->bindParam( ':dialplan_id', $params['dialplan_id'], \PDO::PARAM_INT );

		$param_stmt->execute();

		$remove_dialplan_query = <<<SQL
  DELETE
    FROM extension_dialplan
   WHERE dialplan_id = :dialplan_id
SQL;
		$remove_dialplan_stmt = dbh()->prepare( $remove_dialplan_query );

		$remove_dialplan_stmt->bindParam( ':dialplan_id', $params['dialplan_id'], \PDO::PARAM_INT );

		$remove_dialplan_stmt->execute();

		return build_result( TRUE, 'delete_command', [ 'delete_command' => $params['dialplan_id'] ] );
	}

?>
