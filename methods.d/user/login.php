<?php namespace user;

	function login( $params ) {
	/*
	 * Login
	 * Searches database for a email address and password match.
	 * If found, returns the user's record from the user table.
	 * - email, password
	 */
	
	//  require( "{$_SERVER['DOCUMENT_ROOT']}/../config.php" );
	 require( \env::$paths['methods'] . '/../config.php' );

	 \function_init( [ 'build_result', 'check_api_token', 'dbh', 'bind_params', 'verify_hash', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			// return _build_response( ERROR, 'api_token_missing' );
			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			// return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

		// $pdo = \db_connect();

		audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Get user account from database by name and password
		$user_query = <<<SQL
  SELECT *
    FROM user
   WHERE email     = :username
     AND email    != ''
     AND password != ''
     AND active    = '1'
SQL;
		$user_result = dbh()->prepare( $user_query );
		$user_result->execute( array(
			':username' => $params['username']
		));

		$user_row = $user_result->fetch( \PDO::FETCH_ASSOC );

		$authenticated = FALSE;

		if ( password_verify( $params['password'], $user_row['password'] )) {
		// User account found
			$hash = md5( microtime() . $params['password'] );

			$user_row['hash'] = $hash;

			$login_query = <<<SQL
  UPDATE user
     SET last_login = :last_login,
         hash       = :hash
   WHERE user_id    = :user_id
SQL;
			$login_result = dbh()->prepare( $login_query );
			$login_result->execute( array(
				':last_login' => date_format( date_create(), 'Y-m-d H:i:s' ),
				':hash'       => $hash,
				':user_id'    => $user_row['user_id']
			));

			$authenticated = TRUE;

			$client_query = <<<SQL
  SELECT client.client_id, client.name
    FROM xref_client_user
           INNER JOIN client
                   ON xref_client_user.client_id = client.client_id
   WHERE user_id = :user_id
SQL;
			$client_result = dbh()->prepare( $client_query );
			$client_result->execute( array(
				':user_id' => $user_row['user_id']
			));

			$user_row['clients'] = $client_result->fetchAll( \PDO::FETCH_ASSOC );

		}

		if ( $authenticated ) {
			return build_result( TRUE, 'login_successful', array( 'user' => $user_row ));
		} else {
		// User account not found or bad authentication
			return build_result( FALSE, 'Invalid username and/or password.' );
		}
	}

?>
