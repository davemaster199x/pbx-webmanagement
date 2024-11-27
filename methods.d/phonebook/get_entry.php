<?php namespace phonebook;

	function get_entry( $params ) {
	/*
	 * Get
	 * Retrieve a list of phonebook entries.
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

	// Get phonebook(s)
		if ( empty( $params['entry_id'] )) {
		// All phonebook

			$query = <<<SQL
  SELECT *
    FROM phonebook_entry
   WHERE phonebook_id = :phonebook_id
ORDER BY phonebook_entry.first_name
SQL;

			$stmt = dbh()->prepare( $query );

            $stmt->bindParam( ':phonebook_id', $params['phonebook_id'], \PDO::PARAM_INT );
			$stmt->execute();

			$entries = $stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified phonebook details

            $query = <<<SQL
  SELECT *
    FROM phonebook_entry
   WHERE phonebook_id = :phonebook_id
     AND entry_id     = :entry_id
ORDER BY phonebook_entry.first_name
SQL;

			$stmt = dbh()->prepare( $query );

			$stmt->bindParam( ':phonebook_id', $params['phonebook_id'], \PDO::PARAM_INT );
			$stmt->bindParam( ':entry_id',     $params['entry_id'],     \PDO::PARAM_INT );
			$stmt->execute();

			$entries = $stmt->fetchAll( \PDO::FETCH_ASSOC );
		}

		return build_result( TRUE, 'entries', [ 'entries' => $entries ] );
	}

?>
