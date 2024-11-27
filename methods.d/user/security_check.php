<?php namespace user;

	function security_check( $params ) {
	/*
	 * Security Check
	 * Returns true or false, whether or not the specified user has the specified security level.
	 * - $user_id: specifies the user to check.
	 * - $security: the security level for which to check.
	 */

	// Verify authorized API Token
		require( \env::$paths['methods'] . '/../config.php' );

		\function_init( [ 'dbh', 'build_result', 'check_api_token', 'db_connect', 'verify_hash', 'audit_log' ] );

		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

		audit_log( $user_id, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

		if ( isset( $params['user_id'] )) {

			$user_id = $params['user_id'];
		}

	// Validate fields
		if ( !$params['security'] ) {

			return build_result( FALSE, 'security_not_specified' );
		}

		if ( $params['security'] == 'global' ) {

			$security_query = <<<SQL
  SELECT user.is_global AS security
    FROM user
   WHERE user.user_id = :user_id
SQL;
		}

		$security_stmt = dbh()->prepare( $security_query );

		$security_stmt->bindParam( ':user_id', $user_id, \PDO::PARAM_INT );

		$security_stmt->execute();

		$security_row = $security_stmt->fetch( \PDO::FETCH_ASSOC );

		return build_result( TRUE, 'security', $security_row['security'] );
	}

?>
