<?php

	function pbx_api( $method, $endpoint, $username, $password, $payload ) {
	/**
	 * A shortcut for calling the PBX API.
	 *
	 * @param endpoint array  - The endpoint details.
	 * @param method   string - The method to call.
	 * @param payload  array  - The payload to send.
	 *
	 * @return array - The result of the API call.
	 */

	// Import config
		require( \env::$paths['methods'] . '/../config.php' );

	// Import autoloader
		include_once( __DIR__ . '/pbxapi_autoloader.php' );

		pbxapi_autoloader();

	// Create the new API object
		$api = new \PBX\API();

	// Set the endpoint
		$api->endpoint(
			$endpoint['protocol'],
			$endpoint['server'],
			$endpoint['port'],
			$endpoint['version']
		);

	// Set the credentials
		$api->credentials( $username, $password );

	// Create the new Method object
		$api_method = $api->method( $method );

	// Set the parameters
		if ( in_array( $method, [ 'DeviceCreate', 'DeviceDelete', 'DeviceUpdate' ] )) {

			$api_method->set( 'mac',           $payload['mac'] );
			$api_method->set( 'orig_mac',      $payload['orig_mac'] );
			$api_method->set( 'name',          $payload['name'] );
			$api_method->set( 'device_type',   $payload['device_type'] );
			$api_method->set( 'http_user',     $payload['http_username'] );
			$api_method->set( 'http_password', $payload['http_password'] );
			$api_method->set( 'values',        $payload['values'] );
		} elseif ( $method == 'SIPEndpoints' ) {

			foreach ( $payload as $sip_endpoint ) {

				$api_method->add_endpoint(
					$sip_endpoint['name'],
					$sip_endpoint['password'],
					$sip_endpoint['context'],
					$sip_endpoint['transport'],
					$sip_endpoint['callerid'],
					$sip_endpoint['mailboxes']
				);
			}
		} elseif ( $method == 'Extensions' ) {

			foreach ( $payload as $context => $dialplan ) {

				if ( isset( $dialplan['include'] )) {

					foreach ( $dialplan['include'] as $include ) {

						$api_method->add_include( $context, $include );
					}
				}

				if ( isset( $dialplan['exten'] )) {

					$api_method->add_exten( $context, $dialplan['exten'] );
				}
			}
		} elseif ( $method == 'Voicemail' ) {

			foreach ( $payload as $mailbox ) {

				$api_method->add_mailbox( $mailbox['mailbox'], $mailbox['password'], $mailbox['name'], $mailbox['email'], $mailbox['options'] );
			}
		}

	// Submit the request
		return $api->send();
	}

?>
