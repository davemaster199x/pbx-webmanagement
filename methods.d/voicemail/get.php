<?php namespace voicemail;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of voicemail.
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

	// Get voicemails
		if ( empty( $params['voicemail_id'] )) {
		// Get all voicemails

			$voicemail_query = <<<SQL
  SELECT voicemail.voicemail_id, voicemail.mailbox, voicemail.name As vname, voicemail.email, voicemail.options,
         client.name As cname, client.client_id
    FROM voicemail
        	INNER JOIN client_rpoint
                	ON voicemail.rpoint_id = client_rpoint.rpoint_id
				   		INNER JOIN client
                   			ON client_rpoint.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
SQL;

			if ( !empty( $params['client_id'] )) {

				$voicemail_query .= ' AND client.client_id = :client_id';
			}

			if ( !empty( $params['rpoint_id'] )) {

				$voicemail_query .= ' AND voicemail.rpoint_id = :rpoint_id';
			}

			$voicemail_query .= ' ORDER BY voicemail.mailbox ASC';

			$voicemail_stmt = dbh()->prepare( $voicemail_query );

			if ( !empty( $params['client_id'] )) {
			
				$voicemail_stmt->bindParam( ':client_id', $params['client_id'], \PDO::PARAM_INT );
			}

			if ( !empty( $params['rpoint_id'] )) {
			
				$voicemail_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );
			}

			$voicemail_stmt->execute();

			$voicemail = $voicemail_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified voicemail details

			$voicemail_query = <<<SQL
  SELECT voicemail.*, client.client_id
	FROM voicemail
			INNER JOIN client_rpoint
                	ON voicemail.rpoint_id = client_rpoint.rpoint_id
				   		INNER JOIN client
                   			ON client_rpoint.client_id = client.client_id
	WHERE voicemail_id = :voicemail_id
     AND client.client_id IN ( $client_ids )
SQL;
			$voicemail_stmt = dbh()->prepare( $voicemail_query );

			$voicemail_stmt->bindParam( ':voicemail_id', $params['voicemail_id'], \PDO::PARAM_INT );

			$voicemail_stmt->execute();

			$voicemail = $voicemail_stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'voicemail', [ 'voicemail' => $voicemail ] );
	}

?>
