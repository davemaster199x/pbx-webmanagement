<?php namespace phonebook;

	function save_entry( $params ) {
	/*
	 * Save Entry
	 * Save a phonebook entry.
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

		if ( empty( $params['entry_id'] )) {
		// Save new phonebook entry
			$entry_query = <<<SQL
  INSERT INTO phonebook_entry
     SET phonebook_id = :phonebook_id,
         first_name   = :first_name,
         last_name    = :last_name,
         number       = :number,
         type         = :type
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':phonebook_id' => $params['phonebook_id'],
				':first_name'   => $params['first_name'],
				':last_name'    => $params['last_name'],
				':number'       => $params['number'],
				':type'         => $params['type']
			));

			return _build_response( COMPLETE, 'entry_saved' );
		} else {
		// Update existing phonebook entry
			$entry_query = <<<SQL
  UPDATE phonebook_entry
     SET first_name   = :first_name,
         last_name    = :last_name,
         number       = :number,
         type         = :type
   WHERE phonebook_id = :phonebook_id
     AND entry_id     = :entry_id
SQL;
			$entry_result = $pdo->prepare( $entry_query );
			$entry_result->execute( array(
				':first_name'   => $params['first_name'],
				':last_name'    => $params['last_name'],
				':number'       => $params['number'],
				':type'         => $params['type'],
				':phonebook_id' => $params['phonebook_id'],
				':entry_id'     => $params['entry_id']
			));

			return _build_response( COMPLETE, 'entry_saved' );
		}
	}

?>
