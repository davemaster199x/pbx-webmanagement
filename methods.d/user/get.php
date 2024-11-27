<?php namespace user;

	function get( $params ) {
	/*
	 * Get
	 * Retrieve a list of users.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log' ] );

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

		if ( empty( $params['user_id'] )) {

			$user_query = <<<SQL
  SELECT user.*
    FROM user
   ORDER BY user.active DESC, user.last_name, user.first_name
SQL;
			$user_stmt = dbh()->prepare( $user_query );
			$user_stmt->execute();

			$users = $user_stmt->fetchAll( \PDO::FETCH_ASSOC );
		} else {
		// Get specified user details

			$user_query = <<<SQL
  SELECT user.*
	FROM user
   WHERE user.user_id = :user_id
SQL;
			$user_stmt = dbh()->prepare( $user_query );

			$user_stmt->bindParam( ':user_id', $params['user_id'], \PDO::PARAM_INT );

			$user_stmt->execute();

			$users = $user_stmt->fetchAll( \PDO::FETCH_ASSOC );

			$client_query = <<<SQL
  SELECT client_id
    FROM xref_client_user
   WHERE user_id = :user_id
SQL;
			$client_stmt = dbh()->prepare( $client_query );

			$client_stmt->bindParam( ':user_id', $params['user_id'], \PDO::PARAM_INT );

			$client_stmt->execute();

			$users[0]['clients'] = $client_stmt->fetchAll( \PDO::FETCH_COLUMN );
		}

		return build_result( TRUE, 'users', [ 'users' => $users ] );
	}

?>
