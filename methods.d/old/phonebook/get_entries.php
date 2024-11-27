<?php namespace phonebook;

	function get_entries( $params ) {
	/*
	 * Get Entries
	 * Retrieve a list of phonebook entries.
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

		if ( isset( $params['client_id'] ) && isset( $params['phonebook_id'] )) {
		// Get all phonebooks for client
			$entry_query = <<<SQL
	  SELECT phonebook_entry.*
		FROM phonebook_entry
               INNER JOIN phonebook
                       ON phonebook_entry.phonebook_id = phonebook.phonebook_id
       WHERE phonebook.client_id          = :client_id
         AND phonebook_entry.phonebook_id = :phonebook_id
	   ORDER BY phonebook_entry.first_name, phonebook_entry.last_name, phonebook_entry.number
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':client_id'    => $params['client_id'],
				':phonebook_id' => $params['phonebook_id']
			));

			$entries = $entry_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['phonebook_id'] ) && isset( $params['entry_id'] )) {
		// Get specified route
			$entry_query = <<<SQL
	  SELECT phonebook_entry.*
		FROM phonebook_entry
       WHERE phonebook_entry.phonebook_id = :phonebook_id
         AND phonebook_entry.entry_id     = :entry_id
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':phonebook_id' => $params['phonebook_id'],
				':entry_id'     => $params['entry_id']
			));

			$entries = $entry_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return _build_response( ERROR, 'missing_client_and_phonebook_or_phonebook_and_entry' );
		}

		return _build_response( COMPLETE, 'entry', array( 'entry' => $entries ));
	}

?>
