<?php namespace PBX\API;

	class Asterisk {
	/**
	 * PBX API Asterisk
	 */

		private $endpoints  = [];
		private $extensions = [];
		private $mailboxes  = [];
		private $method     = '';
		private $values     = [];

		public function __construct( $method ) {
		/**
		 * Create a new method object for the API.
		 *
		 * @param method string - The name of the method.
		 *
		 * @return void
		 */

		// Validate method being set
			if ( !in_array( $method, [ 'Extensions', 'SIPEndpoints', 'Voicemail' ] )) {

				throw new \Exception( "Invalid method '$method'." );
			}

		// Set the method
			$this->method = $method;

		// Clear previously set parameters
			$this->values = [];
		}

		public function __get( $name ) {
		/**
		 * Get an allowed private variable (effective "read-only").
		 *
		 * @param name string - The name of the variable to get.
		 *
		 * @return string - The value of the variable.
		 */

			if ( in_array( $name, [ 'method' ] )) {

				return $this->$name;
			}
		}

		public function add_exten( $context, $exten ) {
		/**
		 * Create a dialplan extension entry.
		 *
		 * @param context string - The context to create or add to.
		 * @param exten   array  - An array with extensions as they keys and elements as the commands and parameters.
		 *
		 * @return void
		 */

			if ( !isset( $this->extensions[ $context ] )) {

				$this->extensions[ $context ] = [];
			}

			if ( !isset( $this->extensions[ $context ]['exten'] )) {

				$this->extensions[ $context ]['exten'] = [];
			}

			foreach ( $exten as $ext => $rows ) {

				for ( $i = 0; $i < count( $rows ); ++$i ) {

/*
					if ( $rows[ $i ]['cmd'] == 'Dial' ) {
					// This is a Dial command, parse and format params

						$endpoints = explode( '&', $rows[ $i ]['param'] );

						$endpoints_formatted = [];

						foreach ( $endpoints as $endpoint_delay ) {

							list( $endpoint, $delay ) = explode( ',', $endpoint_delay );

							$endpoints_formatted[] = 'LOCAL/multi_' . str_pad( $delay, 2, '0', STR_PAD_LEFT ) . '_' . $endpoint;
						}

						$rows[ $i ]['param'] = implode( '&', $endpoints_formatted );
					}
*/
				}

				$this->extensions[ $context ]['exten'][ $ext ] = $rows;
			}		
		}

		public function add_include( $context, $include ) {
		/**
		 * Create a dialplan include entry.
		 *
		 * @param context string - The context to create or add to.
		 * @param include string - An include to add to the context.
		 *
		 * @return void
		 */

			if ( !isset( $this->extensions[ $context ] )) {

				$this->extensions[ $context ] = [];
			}

			if ( !isset( $this->extensions[ $context ]['include'] )) {

				$this->extensions[ $context ]['include'] = [];
			}

			if ( !isset( $this->extensions[ $context ]['include'] )) {

				$this->extensions[ $context ]['include'] = [];
			}

			$this->extensions[ $context ]['include'][] = $include;
		}

		public function add_endpoint( $name, $password, $context, $transport = '', $callerid = '', $mailboxes = '' ) {
		/**
		 * Create en entry for the endpoint.
		 *
		 * @param name      string - The name (MAC address) of the endpoint.
		 * @param password  string - The password.
		 * @param context   string - The context.
		 * @param transport string - The transport method.
		 * @param callerid  string - The Caller ID.
		 * @param mailboxes string - The mailboxes subscribed to.
		 */

			$this->endpoints[] = [
				'name'      => $name,
				'password'  => $password,
				'context'   => $context,
				'transport' => $transport,
				'callerid'  => $callerid,
				'mailboxes' => $mailboxes
			];
		}

		public function add_mailbox( $mailbox, $password, $name = '', $email = '', $options = [] ) {
		/**
		 * Create en entry for a mailbox.
		 *
		 * @param mailbox  string - The numerical mailbox.
		 * @param password string - The password.
		 * @param name     string - The name.
		 * @param email    string - The email address.
		 * @param options  array  - A list of options.
		 */

			$this->mailboxes[] = [
				'mailbox'  => $mailbox,
				'password' => $password,
				'name'     => $name,
				'email'    => $email,
				'options'  => $options
			];
		}

		public function method( $method ) {
		/**
		 * Set the method to be called.
		 *
		 * @param method string - The name of the method to set.
		 *
		 * @return void
		 */

		// Validate that the method being set is allowed
			if ( !in_array( $method, [ 'Extensions', 'SIPEndpoints', 'Voicemail' ] )) {

				throw new \Exception( "Invalid method '$method'." );
			}

		// Method is good, set it
			$this->method = $method;

		// Clear previously set parameters
			$this->values = [];
		}

		public function server( $protocol, $server, $port, $version ) {
		/**
		 * Set the API endpoint parameters.
		 *
		 * @param protocol string - Either "http" or "https".
		 * @param server   string - The hostname or IP address of the server.
		 * @param port     int    - The port on which to contact the server.
		 * @param version  float  - The version of the API to call.
		 */

		// Validate that an API endpoint has been sent
			if ( empty( $this->endpoint )) {

				throw new \Exception( 'Set the API endpoint before setting the method.' );
			}

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
			$this->endpoint = $protocol . '://' . $server . ':' . '/api/' . $version . '/';

		// Clear previously set method and parameters
			$this->method = '';
			$this->values = [];
		}

		public function set( $key, $value ) {
		/**
		 * Sets a parameter for submitting to the API.
		 *
		 * @param key   string - The name of the parameter to set.
		 * @param value string - The value to be set.
		 *
		 * @return void
		 */

		// Validate that an API endpoint has been sent
			if ( empty( $this->method )) {

				throw new \Exception( 'Set the API method before setting values.' );
			}

		// Validate values based on the method being set
			$error = FALSE;

			switch ( $this->method ) {

				case 'Extensions' :

				break;

				case 'SIPEndpoints' :

					if ( !in_array( $key, [ 'name', 'password', 'context', 'transport', 'callerid', 'mailboxes' ] )) {

						$error = TRUE;
					}

				break;

				case 'Voicemail' :

				break;

			}
		}

		public function values() {
		/**
		 * Return a JSON-encoded version of the ser values.
		 *
		 * @return string - The set values.
		 */

			switch ( $this->method ) {

				case 'Extensions' :

					return json_encode( $this->extensions );

				break;

				case 'SIPEndpoints' :

					return json_encode( $this->endpoints );

				break;

				case 'Voicemail' :

					return json_encode( $this->mailboxes );

				break;

			}
		}

	}

?>
