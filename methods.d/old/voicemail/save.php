<?php namespace voicemail;

	function save( $params ) {
	/*
	 * Save
	 * Save a mailbox.
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

		if ( empty( $params['voicemail_id'] )) {
		// Save new mailbox
			$voicemail_query = <<<SQL
  INSERT INTO voicemail
     SET client_id  = :client_id,
         mailbox    = :mailbox,
         sms_notify = :sms_notify
SQL;
			$voicemail_result = $pdo->prepare( $voicemail_query );
			$voicemail_result->execute( array(
				':client_id'  => $params['client_id'],
				':mailbox'    => $params['mailbox'],
				':sms_notify' => $params['sms_notify']
			));

			return _build_response( COMPLETE, 'voicemail_saved' );
		} else {
		// Update existing mailbox
			$voicemail_query = <<<SQL
  UPDATE voicemail
     SET sms_notify   = :sms_notify
   WHERE voicemail_id = :voicemail_id
SQL;
			$voicemail_result = $pdo->prepare( $voicemail_query );
			$voicemail_result->execute( array(
				':sms_notify'   => $params['sms_notify'],
				':voicemail_id' => $params['voicemail_id']
			));

			return _build_response( COMPLETE, 'voicemail_saved' );
		}
	}

?>
