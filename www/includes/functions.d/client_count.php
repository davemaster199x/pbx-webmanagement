<?php

	function client_count() {
	/**
	 * Returns the number of clients the logged-in user has access to.
	 *
	 * @return int - The number of clients the user has access to.
	 */

	// Import config
		global $config_client;

	// Set holder so we don't call the method multiple times
		static $client_count = NULL;

		if ( $client_count === NULL ) {

			$jsonrpc_client = new jsonrpc\client();
			$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

			$get_clients = new jsonrpc\method( 'client.get' );
			$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
			$get_clients->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_clients );

			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			$client_count = count( $result[ $get_clients->id ]['data']['clients'] );
		}

		return $client_count;
	}

?>
