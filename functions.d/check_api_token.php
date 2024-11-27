<?php

	function check_api_token( $api_token, $role = NULL ) {
	/**
	 * Validate the API token.
	 *
	 * @param string api_token - The token used to authenticate the JSON-RPC client.
	 * @param string role      - An option role limiter.
	 *
	 * @return bool
	 */

		require( \env::$paths['methods'] . '/../config.php' );

		if ( preg_match( '/^([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})(\$[156][a-z]{0,1}\$[a-zA-Z0-9.\/]+\$[a-zA-Z0-9.\/]+)$/', $api_token, $matches ) ) {

			$api_key = $matches[1];
			$hash    = $matches[2];

			$api_keys = $config_server['api_keys'];

			if ( crypt( $api_keys[ $api_key ]['pass'], $hash ) == $hash ) {

				if ( empty( $role ) || in_array( $role, $api_keys[ $api_key ]['role'] ) ) {

					return TRUE;
				}
			}
		}

		return FALSE;
	}

?>
