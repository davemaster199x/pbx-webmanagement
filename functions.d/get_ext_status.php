<?php

	function get_ext_status( $exts ) {
	/*
	 * Get Ext Status
	 * Returns the extension status from the hints via AMI.
	 */

	// Include AMI
		global $_config;

		require( \env::$paths['methods'] . '/../config.php' );

		// require_once( $config_server['paths']['classes'] . '/asterisk/asterisk-manager-ami.php' );

		\function_init( [ 'build_result', 'check_api_token', 'dbh', 'bind_params', 'verify_hash', 'security_check', 'audit_log', 'get_ext_status' ] );

	// Connect to database
		// $pdo = \db_connect();

	// Get AMI server details for the given extension(s)
		$parsed_extensions = array();

		$ami_query = <<<SQL
  SELECT ami.*
    FROM ami
           INNER JOIN client
                   ON ami.ami_id = client.ami_id
           INNER JOIN extension
                   ON extension.client_id = client.client_id
   WHERE extension.ext     = :ext
     AND extension.context = :context
SQL;
		$ami_result = dbh()->prepare( $ami_query );

		foreach ( $exts as $ext ) {
			list( $extension, $context ) = explode( '@', $ext );

			$ami_result->execute( array(
				':ext'     => $extension,
				':context' => $context
			));

			$ami_row = $ami_result->fetch( PDO::FETCH_ASSOC );

			$parsed_extensions[] = array(
				'extension'    => $extension,
				'context'      => $context,
				'ami_server'   => $ami_row['server'],
				'ami_port'     => $ami_row['port'],
				'ami_username' => $ami_row['username'],
				'ami_password' => $ami_row['password']
			);
		}

		$statuses = array();

//		$current_ami_server = '';
//		$current_ami_port   = 0;

		foreach ( $parsed_extensions as $extension ) {
//			if ( $extension['ami_server'] != $current_ami_server || $extension['ami_port'] || $current_ami_port ) {
				try {
				// Connect to AMI and authenticate

					$ami = new \asterisk\manager\ami( $extension['ami_server'], $extension['ami_port'] );

					$ami->login( $extension['ami_username'], $extension['ami_password'] );

//					$current_ami_server = $extension['ami_server'];
//					$current_ami_port   = $extension['ami_port'];
				} catch ( Exception $e ) {
					$statuses[] = array(
						'ext'     => $extension['extension'],
						'context' => $extension['context'],
						'status'  => 'Server Not Reachable'
					);
				}

				$ami_action = new \asterisk\manager\ami_action( 'ExtensionState' );

				$ami_action->packet( 'Exten',   $extension['extension'] );
				$ami_action->packet( 'Context', $extension['context'] );

				$ami->add_action( $ami_action );
//			}

			try {
				$ami->submit();
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}

//			$ami->parse_response( $ami->response );

			foreach ( $ami->parse_response( $ami->response ) as $response ) {
			// Loop through each response

				$status = array();

				foreach ( $response as $key => $value ) {
				// Loop through values

					if ( $key == 'Exten' ) {
					// Get the ext of this response

						$status['ext'] = $value;
					} elseif ( $key == 'Context' ) {
					// Get the context of this response

						$status['context'] = $value;
					} elseif ( $key == 'Status' ) {
					// Get the status of this response

						$bin_status = array();

						if ( $value == -1 ) {
							$bin_status[] = 'Extension Not Found';
						} elseif ( $value == 0 ) {
							$bin_status[] = 'Idle';
						} else {
							if ( $value & 1 ) {
								$bin_status[] = 'In Use';
							}

							if ( $value & 2 ) {
								$bin_status[] = 'Busy';
							}

							if ( $value & 4 ) {
								$bin_status[] = 'Unavailable';
							}

							if ( $value & 8 ) {
								$bin_status[] = 'Ringing';
							}

							if ( $value & 16 ) {
								$bin_status[] = 'On Hold';
							}
						}

						$status['status'] = implode( '|', $bin_status );
					}
				}

				if ( !empty( $status )) {
					$statuses[] = $status;
				}
			}
		}

		return $statuses;
	}

?>
