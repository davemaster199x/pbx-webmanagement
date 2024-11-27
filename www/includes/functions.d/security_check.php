<?php

	function security_check( $security, $user_id = 0 ) {
	/*
	 * Returns true or false, based on whether the specified user_id has at least
	 * one of the available security options enabled.
	 *
	 * @param security string - A security parameter to check.
	 * @param user_id  int    - The user to check, or the current user if not provided.
	 *
	 * @return bool - TRUE if the user has the specified security, FALSE if not.
	 */

		// require( \env::$paths['methods'] . '/../config.php' );
		include( "{$_SERVER['DOCUMENT_ROOT']}/../config.php" );

		$jsonrpc_client = new jsonrpc\client();
		$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

		$params['api_token'] = $config_client['jsonrpc']['api_token'];
		$params['hash']      = $_SESSION['user']['hash'];
		$params['security']  = $security;

		if ( $user_id != 0 ) {

			$params['user_id'] = $user_id;
		}

		$security = new jsonrpc\method( 'user.security_check' );
		$security->id( $jsonrpc_client->generate_unique_id() );
		$security->param( $params );

		$jsonrpc_client->method( $security );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		return $result[ $security->id ]['data'];
    }

?>
