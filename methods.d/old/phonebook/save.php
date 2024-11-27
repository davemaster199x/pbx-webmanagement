<?php namespace phonebook;

	function save( $params ) {
	/*
	 * Save
	 * Save a phonebook.
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

		if ( empty( $params['phonebook_id'] )) {
		// Save new phonebook
			$phonebook_query = <<<SQL
  INSERT INTO phonebook
     SET client_id = :client_id,
         name      = :name
SQL;
			$phonebook_result = $pdo->prepare( $phonebook_query );
			$phonebook_result->execute( array(
				':client_id' => $params['client_id'],
				':name'      => $params['name']
			));

			return _build_response( COMPLETE, 'phonebook_saved' );
		} else {
		// Update existing phonebook
			$phonebook_query = <<<SQL
  UPDATE phonebook
     SET name         = :name
   WHERE phonebook_id = :phonebook_id
SQL;
			$phonebook_result = $pdo->prepare( $phonebook_query );
			$phonebook_result->execute( array(
				':name'         => $params['name'],
				':phonebook_id' => $params['phonebook_id']
			));

			return _build_response( COMPLETE, 'phonebook_saved' );
		}
	}

?>
