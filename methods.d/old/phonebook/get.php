<?php namespace phonebook;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of phonebooks.
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
		// Get all phonebooks for client
			$phonebook_query = <<<SQL
	  SELECT phonebook.*
		FROM phonebook
       WHERE phonebook.client_id = :client_id
	   ORDER BY phonebook.name
SQL;
			$phonebook_result = $pdo->prepare( $phonebook_query );
			$phonebook_result->execute( array(
				':client_id' => $params['client_id']
			));

			$phonebook = $phonebook_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['phonebook_id'] )) {
		// Get specified route
			$phonebook_query = <<<SQL
	  SELECT phonebook.*
		FROM phonebook
       WHERE phonebook.phonebook_id = :phonebook_id
SQL;
			$phonebook_result = $pdo->prepare( $phonebook_query );
			$phonebook_result->execute( array(
				':phonebook_id' => $params['phonebook_id']
			));

			$phonebook = $phonebook_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_or_phonebook' );
		}

		return _build_response( COMPLETE, 'phonebook', array( 'phonebook' => $phonebook ));
	}

?>
