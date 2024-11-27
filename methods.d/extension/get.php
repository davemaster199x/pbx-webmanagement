<?php namespace extension;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of extension.
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
		// All extensions

			$extension_query = <<<SQL
  SELECT extension.*,
         client_rpoint.rpoint_id, client_rpoint.name AS rpoint_name,
         client.client_id, client.name AS client_name
    FROM extension
           INNER JOIN client_rpoint
                   ON extension.rpoint_id = client_rpoint.rpoint_id
           INNER JOIN client
                   ON client_rpoint.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
   ORDER BY client_name, rpoint_name, extension.ext
SQL;

			if ( !empty( $params['client_id'] )) {

				$extension_query .= ' AND client.client_id = :client_id';
			}

			$extension_stmt = dbh()->prepare( $extension_query );

			if ( !empty( $params['client_id'] )) {
			
				$extension_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			}

			$extension_stmt->execute();

			$extension = $extension_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified extension details

			$extension_query = <<<SQL
  SELECT extension.*,
         client_rpoint.client_id
	FROM extension
           INNER JOIN client_rpoint
                   ON extension.rpoint_id = client_rpoint.rpoint_id
   WHERE extension_id = :extension_id
     AND client_rpoint.client_id IN ( $client_ids )
ORDER BY extension.ext
SQL;
			$extension_stmt = dbh()->prepare( $extension_query );

			$extension_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

			$extension_stmt->execute();

			$extension = $extension_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'extension', [ 'extension' => $extension ] );
	}

?>
