<?php namespace client;

	function get_extension( $params ) {
	/*
	 * Get Extension
	 * Retrieve a list of extensions.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'bind_params', 'verify_hash', 'security_check', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

		// $pdo = \db_connect();

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );
		if ( !$user_id ) {
			return build_result( FALSE, 'invalid_hash' );
		}

		if ( isset( $params['client_id'] )) {
		// Get all extensions for client
			$extension_query = <<<SQL
	  SELECT extension.*
		FROM extension
       WHERE extension.client_id = :client_id
	   ORDER BY extension.ext
SQL;
			$extension_result = dbh()->prepare( $extension_query );
			$extension_result->execute( array(
				':client_id' => $params['client_id']
			));

			$extensions = $extension_result->fetchAll( \PDO::FETCH_ASSOC );
		} elseif ( isset( $params['extension_id'] )) {
		// Get specific extension
			$extension_query = <<<SQL
	  SELECT extension.*
		FROM extension
       WHERE extension.extension_id = :extension_id
SQL;
			$extension_result = dbh()->prepare( $extension_query );
			$extension_result->execute( array(
				':extension_id' => $params['extension_id']
			));

			$extensions = $extension_result->fetchAll( \PDO::FETCH_ASSOC );
		} else {
			return build_result( FALSE, 'missing_client_or_extension' );
		}

		return build_result( TRUE, 'extensions', array( 'extensions' => $extensions ));
	}

?>
