<?php namespace asterisk\manager;

	class ami {

		private $actions  = array();
		private $events   = FALSE;
		private $server   = '';
		private $port     = 0;
		private $user     = '';
		private $password = '';

		public $response;

		public function __construct( $server, $port = 5038, $events = FALSE ) {
		/*
		 * __construct
		 * Instantiates the class.
		 */

			if ( empty( $server )) {
				$this->server = 'localhost';
			} else {
				$this->server = $server;
			}

			$this->port   = $port;
			$this->events = $events;
		}

		public function login( $user, $password ) {
		/*
		 * Login
		 * Logs into the AMI.
		 */

			$this->user     = $user;
			$this->password = $password;
		}

        public function add_action( $action ) {
        /*
         * Add Action
         * Adds an action specified by the ami_action class.
         */

            $this->actions[] = $action->values();
        }

		public function submit() {
		/*
		 * Submit
		 * Submits the queued actions.
		 */

			$crlf = "\r\n";

			if ( !count( $this->actions )) {
				throw new \Exception( 'No actions specified.' );
			}

			$ami = @fsockopen( $this->server, $this->port, $errno, $errstr, 5 );

			if ( !$ami ) {
error_log( $this->server );
				throw new \Exception( 'Could not connect to AMI.' );
			} else {
				if ( !empty( $this->user )) {
					fputs( $ami, 'Action: login' . $crlf );
					fputs( $ami, 'Username: ' . $this->user . $crlf );
					fputs( $ami, 'Secret: ' . $this->password . $crlf );

					if ( !$this->events ) {
						fputs( $ami, 'Events: off' . $crlf );
					}

					fputs( $ami, $crlf );
				}

				foreach ( $this->actions as $action ) {
					fputs( $ami, 'Action: ' . $action['action'] . $crlf );

					foreach ( $action['packets'] as $key => $value ) {
						if ( empty( $value )) {
							fputs( $ami, $key . $crlf );
						} else {
							fputs( $ami, $key . ': ' . $value . $crlf );
						}
					}

					fputs( $ami, $crlf );
				}

				if ( !empty( $this->user )) {
					fputs( $ami, 'Action: logoff' . $crlf . $crlf );
				}

				while ( !feof( $ami )) {
					$this->response .= fgets( $ami, 1024 );
				}

				fclose( $ami );
			}

		}

		public function parse_response( $response ) {
		/*
		 * Parse Response
		 * Parses the AMI response into key/value pairs.
		 */

			$parsed_response = array();

			foreach ( explode( "\r\n\r\n", $response ) as $i => $ind_resp ) {

			// Loop through each response
				$values = array();

				foreach ( explode( "\r\n", $ind_resp ) as $line ) {

				// Split into individual values and assign to variables
					$split = explode( ': ', $line );

					if ( isset( $split[1] )) {
						$values[ $split[0] ] = $split[1];
					} else {
						$values['AMI'] = $split[0];
					}
				}

				$parsed_response[] = $values;
			}

			return $parsed_response;
		}

	}

	class ami_action {

		private $action  = '';
		private $packets = array();

		public function __construct( $action ) {
		/*
		 * __construct
		 * Instantiates the class.
		 */

			if ( empty( $action )) {
				throw new \Exception( 'No action specified.' );
			} else {
				$this->action = $action;
			}
		}

		public function packet( $key, $value = '' ) {
		/*
		 * Packet
		 * Adds a packet to this action.
		 */

			$this->packets[ $key ] = $value;
		}

		public function values() {
		/*
		 * Values
		 * Called by the SIP class to retrieve set parameters.
		 */

			return array(
				'action'  => $this->action,
				'packets' => $this->packets
			);
        }
		
	}

?>
