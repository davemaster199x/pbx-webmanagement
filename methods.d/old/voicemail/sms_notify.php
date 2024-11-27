<?php namespace voicemail;

	function sms_notify( $params ) {
	/*
	 * SMS Notify
	 * Retrieve the sms_notify destination of a maibox.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

		if ( !isset( $params['client_id'] )) {
			return _build_response( ERROR, 'client_id_not_specified' );
		}

		if ( !isset( $params['mailbox'] )) {
			return _build_response( ERROR, 'mailbox_not_specified' );
		}

		$voicemail_query = <<<SQL
  SELECT voicemail.sms_notify
	FROM voicemail
   WHERE voicemail.client_id = :client_id
     AND voicemail.mailbox   = :mailbox
SQL;
		$voicemail_result = $pdo->prepare( $voicemail_query );
		$voicemail_result->execute( array(
			':client_id' => $params['client_id'],
			':mailbox'   => $params['mailbox']
		));

		$voicemail = $voicemail_result->fetch( \PDO::FETCH_ASSOC );

		return _build_response( COMPLETE, 'mailbox', array( 'mailbox' => $voicemail ));
	}

?>
