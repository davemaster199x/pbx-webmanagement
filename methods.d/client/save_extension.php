<?php namespace client;

	function save_extension( $params ) {
	/*
	 * Save Extension
	 * Save an extension.
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

		$global_query = <<<SQL
  SELECT user.is_global
    FROM user
   WHERE user.user_id = :user_id
SQL;
		$global_result = dbh()->prepare( $global_query );
		$global_result->execute( array(
			':user_id' => $user_id
		));

		$global_row = $global_result->fetch( \PDO::FETCH_ASSOC );

		if ( !$global_row['is_global'] ) {
			return build_result( FALSE, 'access_denied' );
		}

		if ( isset( $params['extension_id'] )) {
		// Update existing extension
			$extension_query = <<<SQL
  UPDATE extension
     SET ext          = :ext,
         context      = :context
   WHERE extension_id = :extension_id
SQL;
			$extension_result = dbh()->prepare( $extension_query );

			$bind = array(
				':ext'          => $params['ext'],
				':context'      => $params['context'],
				':extension_id' => $params['extension_id']
			);

			if ( $extension_result->execute( $bind )) {
				return build_result( TRUE, 'extension_saved', array( 'extension_id' => $params['extension_id'] ));
			} else {
				return build_result( FALSE, 'extension_not_saved', array( 'error' => $extension_result->errorInfo() ));
			}
		} else {
		// Create new extension
			$extension_query = <<<SQL
  INSERT INTO extension
     SET client_id = :client_id,
         ext       = :ext,
         context   = :context
SQL;
			$extension_result = dbh()->prepare( $extension_query );

			$bind = array(
				':client_id' => $params['client_id'],
				':ext'       => $params['ext'],
				':context'   => $params['context']
			);

			if ( $extension_result->execute( $bind )) {
				return build_result( TRUE, 'extension_saved', array( 'extension_id' => dbh()->lastInsertId() ));
			} else {
				return build_result( FALSE, 'extension_not_saved', array( 'error' => $extension_result->errorInfo() ));
			}
		}
	}

?>
