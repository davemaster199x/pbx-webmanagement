<?php namespace phonebook;

	function delete_entry( $params ) {
	/*
	 * Delete Entry
	 * Delete a phonebook entry.
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

		$delete_query = <<<SQL
  DELETE
    FROM phonebook_entry
   WHERE phonebook_id = :phonebook_id
     AND entry_id     = :entry_id
SQL;
		$delete_result = $pdo->prepare( $delete_query );
		$delete_result->execute( array(
			':phonebook_id' => $params['phonebook_id'],
			':entry_id'     => $params['entry_id']
		));

		return _build_response( COMPLETE, 'entry_deleted' );
	}

?>
