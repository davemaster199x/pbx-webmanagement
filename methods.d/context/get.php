<?php namespace context;

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

	// Get client ID access
		$client_ids = implode( ',', client_access( $user_id ));

	// Get contexts
		if ( empty( $params['context_id'] )) {
		// Get all contexts

			$context_query = <<<SQL
  SELECT context.*, client.name As cname, client.client_id, client_rpoint.name As clname
    FROM context
        	INNER JOIN client_rpoint
                	ON context.rpoint_id = client_rpoint.rpoint_id
				   		INNER JOIN client
                   			ON client_rpoint.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
SQL;

			if ( !empty( $params['client_id'] )) {

				$context_query .= ' AND client.client_id = :client_id';
			}

			if ( !empty( $params['rpoint_id'] )) {

				$context_query .= ' AND context.rpoint_id = :rpoint_id';
			}

			$context_stmt = dbh()->prepare( $context_query );

			if ( !empty( $params['client_id'] )) {
			
				$context_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			}

			if ( !empty( $params['rpoint_id'] )) {

				$context_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );
			}

			$context_stmt->execute();

			$context = $context_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified context details

			$context_query = <<<SQL
  SELECT context.*, client.name As cname, client.client_id, client_rpoint.name As clname
    FROM context
        	INNER JOIN client_rpoint
                	ON context.rpoint_id = client_rpoint.rpoint_id
				   		INNER JOIN client
                   			ON client_rpoint.client_id = client.client_id
	WHERE context.context_id = :context_id
     AND client.client_id IN ( $client_ids )
SQL;
			$context_stmt = dbh()->prepare( $context_query );

			$context_stmt->bindParam( ':context_id', $params['context_id'], \PDO::PARAM_INT );

			$context_stmt->execute();

			$context = $context_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

	// Get child relationships
		$child_query = <<<SQL
  SELECT context.context_id, context.context
    FROM xref_context_context
           INNER JOIN context
                   ON xref_context_context.child_id = context.context_id
   WHERE xref_context_context.parent_id = :parent_id
SQL;
		$child_stmt = dbh()->prepare( $child_query );

		for ( $i = 0; $i < count( $context ); ++$i ) {

			$context[ $i ]['children'] = [];

			$child_stmt->bindParam( ':parent_id', $context[ $i ]['context_id'], \PDO::PARAM_INT );

			$child_stmt->execute();

			$context[ $i ]['children'] = $child_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'context', [ 'context' => $context ] );
	}

?>
