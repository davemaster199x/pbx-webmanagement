<?php namespace PBX;

	class API {
	/**
	 * PBX API
	 */

		private $endpoint = '';
		private $method   = '';
		private $password = '';
		private $username = '';

		public function credentials( $username, $password ) {
		/**
		 * Sets the credentials for the endpoint.
		 *
		 * @param username string - The username for the endpoint.
		 * @param password string - The password for the endpoint.
		 *
		 * @return void
		 */

			if ( empty( $username )) {

				throw new \Exception( 'The username cannot be blank.' );
			}

			if ( empty( $password )) {

				throw new \Exception( 'The password cannot be blank.' );
			}

			$this->username = $username;
			$this->password = $password;
		}

		public function endpoint( $protocol, $server, $port, $version ) {
		/**
		 * Set the API endpoint parameters.
		 *
		 * @param protocol string - Either "http" or "https".
		 * @param server   string - The hostname or IP address of the server.
		 * @param port     int    - The port on which to contact the server.
		 * @param version  float  - The version of the API to call.
		 */

		// Validate protocol
			if ( !in_array( $protocol, [ 'http', 'https' ] )) {

				throw new \Exception( "Invalid protocol '$protocol'." );
			}

		// Validate that the port is validate port number
			if ( $port < 1 || $port > 65535 ) {

				throw new \Exception( "Invalid port number '$port'." );
			}

		// Validate that the version is valid
			if ( !in_array( $version, [ '1.0' ] )) {

				throw new \Exception( "Invalid API version '$version'." );
			}

		// Parameters are well-formed, set the endpoint URL
			$this->endpoint = $protocol . '://' . $server . ':' . $port . '/api/' . $version . '/';

		// Clear previously set credentials
			$this->username = '';
			$this->password = '';

		// Clear previously set method and parameters
			$this->method = '';
		}

		public function method( $method ) {
		/**
		 * Set the method to be called.
		 *
		 * @param method string - The name of the method to set.
		 *
		 * @return object - An instance of the method class.
		 */

		// Validate that an API endpoint has been sent
			if ( empty( $this->endpoint )) {

				throw new \Exception( 'Set the API endpoint before setting the method.' );
			}

		// Validate that the method being set is allowed
			if ( !in_array( $method, [ 'DeviceCreate', 'DeviceDelete', 'DeviceUpdate', 'Extensions', 'SIPEndpoints', 'Voicemail' ] )) {

				throw new \Exception( "Invalid method '$method'." );
			}

		// Method is good, set it from the correct class
			if ( in_array( $method, [ 'DeviceCreate', 'DeviceDelete', 'DeviceUpdate' ] )) {

				$this->method = new API\Config( $method );
			} elseif ( in_array( $method, [ 'Extensions', 'SIPEndpoints', 'Voicemail' ] )) {

				$this->method = new API\Asterisk( $method );
			}

			return $this->method;
		}

		public function send() {
		/**
		 * Send the request
		 *
		 * @return void
		 */

		// Validate that an endpoint has been set
			if ( empty( $this->endpoint )) {

				throw new \Exception( 'An endpoint must be set before sending the request.' );
			}

		// Validate that a method has been set
			if ( empty( $this->method )) {

				throw new \Exception( 'A method must be set before sending the request.' );
			}

		// Submit the request
			error_log( 'DEBUG: Sending data to API Endpoint ' . $this->endpoint );

			$result = $this->submit( $this->username, $this->password, $this->endpoint . $this->method->method, $this->method->values() );

			error_log( 'DEBUG: Submitted.' );

		// Parse and handle result
			$parsed = @json_decode( $result, TRUE );

			if ( $parsed === NULL && json_last_error() !== JSON_ERROR_NONE ) {

				return json_last_error_msg();
			} else {

				return $parsed;
			}
		}

		private function submit( $username, $password, $endpoint, $payload ) {
		/**
		 * Make an API call to one of the PBX APIs.
		 *
		 * @param string username - The username to authenticate.
		 * @param string password - The password to authenticate.
		 * @param string endpoint - The endpoint to send the request to.
		 * @param string payload  - The payload to be sent.
		 *
		 * @return string - The response from the API endpoint.
		 */

		// Initialize cURL and send request to server
			$ch = curl_init();

		// Create request
			curl_setopt( $ch, CURLOPT_URL,            $endpoint );
			curl_setopt( $ch, CURLOPT_HEADER,         TRUE );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt( $ch, CURLOPT_USERPWD,        "$username:$password" );
			curl_setopt( $ch, CURLOPT_POST,           TRUE );
			curl_setopt( $ch, CURLOPT_POSTFIELDS,     $payload );

		// Submit request
			$data = curl_exec( $ch );

		// Handle response
			$header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
			$headers     = substr( $data, 0, $header_size );
			$body        = substr( $data, $header_size );

			if ( curl_error( $ch )) {
			// An error occurred

				curl_close( $ch );

				return $body;
			} else {
			// Response was good

				curl_close( $ch );

				return $body;
			}
		}

	}

?>
