<?php namespace user;

	function go2admin( $params ) {
	/*
	 * Go2Admin
	 * Validates an admin code, and redirects to the admin login page.
	 * - code
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

	// See if user is an admin
		$user_query = <<<SQL
  SELECT user_id
    FROM user
   WHERE code   = :code
     AND active = '1'
     AND (
              is_global = 1
           OR is_admin  = 1
         )
SQL;
		$user_result = $pdo->prepare( $user_query );
		$user_result->execute( array(
			':code' => $params['code']
		));

		if ( $user_result->rowCount() ) {
			return _build_response( COMPLETE, 'go2admin' );
		} else {
			return _build_response( ERROR, 'Invalid code.' );
		}
	}

?>
