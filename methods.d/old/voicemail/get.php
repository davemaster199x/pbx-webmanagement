<?php namespace voicemail;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of voicemail boxes.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );
		if ( !$user_id ) {
			return _build_response( ERROR, 'invalid_hash' );
		}

		if ( isset( $params['client_id'] )) {
		// Get all mailboxes for client
			$voicemail_query = <<<SQL
	  SELECT voicemail.*
		FROM voicemail
       WHERE voicemail.client_id = :client_id
	   ORDER BY voicemail.mailbox
SQL;
			$voicemail_result = $pdo->prepare( $voicemail_query );
			$voicemail_result->execute( array(
				':client_id' => $params['client_id']
			));

			$voicemail = $voicemail_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['voicemail_id'] )) {
		// Get specified route
			$voicemail_query = <<<SQL
	  SELECT voicemail.*
		FROM voicemail
       WHERE voicemail.voicemail_id = :voicemail_id
SQL;
			$voicemail_result = $pdo->prepare( $voicemail_query );
			$voicemail_result->execute( array(
				':voicemail_id' => $params['voicemail_id']
			));

			$voicemail = $voicemail_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_route' );
		}

		return _build_response( COMPLETE, 'voicemail', array( 'voicemail' => $voicemail ));
	}

?>
