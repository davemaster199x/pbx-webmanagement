<?php namespace context_children;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of context.
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

		if ( !empty( $params['context_id'] )) {
		// Get all contexts children

			$context_query = <<<SQL
  SELECT *
    FROM xref_context_context
   WHERE parent_id = :parent_id
SQL;

			$context_stmt = dbh()->prepare( $context_query );
            $context_stmt->bindParam( ':parent_id', $params['context_id'], \PDO::PARAM_INT );
			$context_stmt->execute();

			$context_child = $context_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'context_children', [ 'context_children' => $context_child ] );
	}

?>
