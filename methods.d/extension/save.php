<?php namespace extension;

	function save( $params ) {
	/*
	 * Save
	 * Save a extension.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log', 'pbx_api' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

		if ( empty( $params['extension_id'] )) {
		// Save extension

			$extension_query = <<<SQL
  INSERT INTO extension
     SET rpoint_id   = :rpoint_id,
         context_id  = :context_id,
         ext         = :ext
SQL;
			$extension_stmt = dbh()->prepare( $extension_query );

			$extension_stmt->bindParam( ':rpoint_id',  $params['rpoint_id'],  \PDO::PARAM_INT );
			$extension_stmt->bindParam( ':context_id', $params['context_id'], \PDO::PARAM_INT );
			$extension_stmt->bindParam( ':ext',        $params['ext'],        \PDO::PARAM_STR );

			if ( $extension_stmt->execute() ) {

				return build_result( TRUE, 'extension_saved', [ 'extension_id' => dbh()->lastInsertId() ] );
			} else {

				return build_result( FALSE, 'extension_not_saved', [ 'error' => $extension_stmt->errorInfo() ] );
			}
		} else {
		// Update existing extension

			$pdo = dbh();

			$pdo->beginTransaction();

			$dialplan_query = <<<SQL
  SELECT extension_dialplan.*
	FROM extension_dialplan
   WHERE extension_id = :extension_id
SQL;
			$dialplan_stmt = dbh()->prepare( $dialplan_query );

			$dialplan_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

			$dialplan_stmt->execute();

			$dialplans = $dialplan_stmt->fetchAll( \PDO::FETCH_ASSOC );
		
		// Remove commands/param first
			foreach ( $dialplans as $dialplan ) {

				$param_query = <<<SQL
  DELETE
    FROM extension_dialplan_param
   WHERE dialplan_id = :dialplan_id
SQL;
				$param_stmt = dbh()->prepare( $param_query );

				$param_stmt->bindParam( ':dialplan_id', $dialplan['dialplan_id'], \PDO::PARAM_INT );

				$param_stmt->execute();
			}

			$remove_dialplan_query = <<<SQL
  DELETE
    FROM extension_dialplan
   WHERE extension_id = :extension_id
SQL;
			$remove_dialplan_stmt = dbh()->prepare( $remove_dialplan_query );

			$remove_dialplan_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

			$remove_dialplan_stmt->execute();

		// End of Remove commands/param first

			$extension_query = <<<SQL
  UPDATE extension
     SET rpoint_id    = :rpoint_id,
         context_id   = :context_id,
         ext          = :ext
   WHERE extension_id = :extension_id
SQL;
			$extension_stmt = $pdo->prepare( $extension_query );

			$extension_stmt->bindParam( ':rpoint_id',    $params['rpoint_id'],    \PDO::PARAM_INT );
			$extension_stmt->bindParam( ':context_id',   $params['context_id'],   \PDO::PARAM_INT );
			$extension_stmt->bindParam( ':ext',          $params['ext'],          \PDO::PARAM_STR );
			$extension_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

			if ( !$extension_stmt->execute() ) {

				return build_result( FALSE, 'extension_not_saved', [ 'error' => $extension_stmt->errorInfo() ] );
			}

			$selected_values = isset( $params['cmd'] ) ? $params['cmd'] : []; 

			$next_prio = 0;

			foreach ( $selected_values as $index => $selected_command ) {
			
				$prio = $index + 1;

				$order = $index + 1;

				if ( $next_prio != 0 ) {

					$order = $order + 1;
				}
			
				$extension_dialplan_query = <<<SQL
  INSERT INTO extension_dialplan
     SET extension_id = :extension_id,
	     prio         = :prio,
         cmd          = :cmd
SQL;
				$extension_dialplan_stmt = $pdo->prepare( $extension_dialplan_query );

				$extension_dialplan_stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );
				$extension_dialplan_stmt->bindParam( ':prio',         $prio,                   \PDO::PARAM_STR );
				$extension_dialplan_stmt->bindParam( ':cmd',          $selected_command,        \PDO::PARAM_STR );

				$extension_dialplan_stmt->execute();

				$dialplan_id = $pdo->lastInsertId();

				$unrecognized = TRUE;

				if ( $selected_command == 'Dial' ) {
				
					if ( isset( $params['endpoint' . $index] ) && is_array( $params['endpoint' . $index] ) ) {
					
						$ringdelay      = $params[ 'ringdelay' . $index ];
						$endpoint_param = '';
						$dial_input     = $params['dial_input' . $index];

						foreach ( $params['endpoint' . $index ] as $index => $dial ) {

							if ( $ringdelay[ $index ] !== '00' ) {

								if ( $ringdelay[ $index ] == '0' ) {

									$delay = '00';
								} else {

									$delay = $ringdelay[ $index ];
								}

								$endpoint_param .= 'LOCAL/multi_' . $delay . '_' . $dial . '&';
							}
						}

						$endpoint_params = rtrim( $endpoint_param, '&' );
					
						$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id,     \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $order,           \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $endpoint_params, \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						if ( $dial_input != '' ) {
							$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id,  \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $order,        \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $dial_input,   \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					}
				} else if ( $selected_command == 'Goto' ) {

					$param = $params['ext_command' . $index ];
				} else if ( $selected_command == 'GotoIfTime' ) {

					$param = ( $params['goto_time' . $index ] != '' ) ? $params['goto_time' . $index ] : "*";
				} else if ( $selected_command == 'HangUp' ) {

					$param = '';
				} else if ( $selected_command == 'PlayBack' ) {

					$param = $params['sound_file' . $index ];
				} else if ( $selected_command == 'VoiceMail' ) {

					$param = $params['mailbox' . $index ];
				} else if ( $selected_command == 'VoiceMailMain' ) {

					$param = $params['voice_mailbox' . $index ];
				} else if ( $selected_command == 'Wait' ) {

					$param = $params['seconds' . $index ];
				} else if ( $selected_command == 'WaitExten' ) {

					$param = $params['seconds_exten' . $index ];
				} else if ( $selected_command == 'Background' ) {

					$param = $params['sound_file_bg' . $index ];
				} else if ( $selected_command == 'Log' ) {

					$param = $params['log_level' . $index ];
				} else if ( $selected_command == 'Set' ) {

					$param = $params['variable' . $index ];
				} else if ( $selected_command == 'Page' ) {

					if ( isset( $params['endpoint_page' . $index ] ) && is_array( $params['endpoint_page' . $index ] )) {
					
						$page_input     = $params['page_input' . $index ];
						$endpoint_param = '';

						foreach ( $params['endpoint_page' . $index ] as $index => $endpoint_page ) {

							$endpoint_param .= 'PJSIP/' . $endpoint_page . '&';
						}

						$endpoint_param = rtrim( $endpoint_param, '&' );

						$endpoint_params = $endpoint_param;

						$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id,     \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $order,           \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $endpoint_params, \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						if ( $page_input != '' ) {

							$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $order,       \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $page_input,  \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					}
				} else {
				// Unrecognized dialplan
				
					$unrecognized       = FALSE;
					$concatenated_value = $selected_command . $index;

					if ( isset( $params[ $concatenated_value ] ) && is_array( $params[ $concatenated_value ] ) ) {

						foreach ( $params[ $concatenated_value ] as $index => $unrecognized_dial ) {

							$order = $index + 1;

							$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id,       \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $order,             \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $unrecognized_dial, \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					}
				}
			
				if ( $selected_command != 'Dial' && $selected_command != 'Page' && $unrecognized ) {

					$extension_dialplan_param_query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param
SQL;
					$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

					$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
					$extension_dialplan_param_stmt->bindParam( ':order',       $order,       \PDO::PARAM_STR );
					$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

					$extension_dialplan_param_stmt->execute();

					if ( $selected_command == 'Goto' ) {
					
						if ( $params['prio' . $index ] != '' ) {
				
							$param = $params['prio' . $index ];

							$next_prio = $order + 1;

							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					} elseif ( $selected_command == 'Log' ) {

						if ( $params['log' . $index ] != '' ) {
				
							$param = $params['log' . $index ];

							$next_prio = $order + 1;

							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					} elseif ( $selected_command == 'GotoIfTime' ) {
				
						$param = ( $params['goto_day_week' . $index ] != '' ) ? $params['goto_day_week' . $index ] : "*";

						$next_prio = $order + 1;

						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						$param = ( $params['goto_day_month' . $index ] != '' ) ? $params['goto_day_month' . $index ] : "*";

						$next_prio = $next_prio + 1;

						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						$param = ( $params['goto_month' . $index ] != '' ) ? $params['goto_month' . $index ] : "*";

						$next_prio = $next_prio + 1;

						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						$param = $params['goto_true' . $index ] ?? "";

						$next_prio = $next_prio + 1;

						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();

						$param = $params['goto_false' . $index ] ?? "";

						$next_prio = $next_prio + 1;

						$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

						$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
						$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
						$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

						$extension_dialplan_param_stmt->execute();
					} elseif ( $selected_command == 'VoiceMailMain' ) {

						if ( $params['voice_mailbox_text' . $index ] != '' ) {
				
							$param = $params['voice_mailbox_text' . $index ];

							$next_prio = $order + 1;

							$extension_dialplan_param_stmt = $pdo->prepare( $extension_dialplan_param_query );

							$extension_dialplan_param_stmt->bindParam( ':dialplan_id', $dialplan_id, \PDO::PARAM_INT );
							$extension_dialplan_param_stmt->bindParam( ':order',       $next_prio,   \PDO::PARAM_STR );
							$extension_dialplan_param_stmt->bindParam( ':param',       $param,       \PDO::PARAM_STR );

							$extension_dialplan_param_stmt->execute();
						}
					}
				}
			}

		// Get all dialplan details
			$client_query = <<<SQL
  SELECT client_id
    FROM client_rpoint
   WHERE rpoint_id = :rpoint_id
SQL;
			$client_stmt = dbh()->prepare( $client_query );

			$client_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

			$client_stmt->execute();

			$client_row = $client_stmt->fetch( \PDO::FETCH_ASSOC );

			$context_query = <<<SQL
  SELECT context.context_id, context.rpoint_id, context.context
    FROM context
           INNER JOIN client_rpoint
                   ON context.rpoint_id = client_rpoint.rpoint_id
   WHERE client_rpoint.client_id = :client_id
   ORDER BY context.context
SQL;
			$context_stmt = dbh()->prepare( $context_query );

			$include_query = <<<SQL
  SELECT context.context_id, context.context
    FROM context
           INNER JOIN xref_context_context
                   ON context.context_id = xref_context_context.child_id
   WHERE xref_context_context.parent_id = :context_id
   ORDER BY context.context
SQL;
			$include_stmt = dbh()->prepare( $include_query );

			$extension_query = <<<SQL
  SELECT extension.extension_id, extension.rpoint_id, extension.ext
    FROM extension
           INNER JOIN client_rpoint
                   ON extension.rpoint_id = client_rpoint.rpoint_id
   WHERE client_rpoint.client_id = :client_id
     AND extension.context_id    = :context_id
   ORDER BY extension.ext
SQL;
			$extension_stmt = dbh()->prepare( $extension_query );

			$dialplan_query = <<<SQL
  SELECT extension_dialplan.dialplan_id, extension_dialplan.cmd
    FROM extension_dialplan
   WHERE extension_dialplan.extension_id = :extension_id
   ORDER BY extension_dialplan.prio
SQL;
			$dialplan_stmt = dbh()->prepare( $dialplan_query );

			$dparam_query = <<<SQL
  SELECT extension_dialplan_param.param
    FROM extension_dialplan_param
   WHERE extension_dialplan_param.dialplan_id = :dialplan_id
SQL;
			$dparam_stmt = dbh()->prepare( $dparam_query );

		// Get all contexts for this client
			$context_stmt->bindParam( ':client_id', $client_row['client_id'], \PDO::PARAM_INT );

			$context_stmt->execute();

			$dialplan_payload = [];

			while ( $context_row = $context_stmt->fetch( \PDO::FETCH_ASSOC )) {

				$include_stmt->bindParam( ':context_id', $context_row['context_id'], \PDO::PARAM_INT );

				$include_stmt->execute();

				while ( $include_row = $include_stmt->fetch( \PDO::FETCH_ASSOC )) {

					if ( !isset( $dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'] )) {

						$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'] = [];
					}

					$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'][] = $include_row['context'];
				}

				$extension_stmt->bindParam( ':client_id',  $client_row['client_id'],   \PDO::PARAM_INT );
				$extension_stmt->bindParam( ':context_id', $context_row['context_id'], \PDO::PARAM_INT );

				$extension_stmt->execute();

				while ( $extension_row = $extension_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$dialplan_stmt->bindParam( ':extension_id', $extension_row['extension_id'], \PDO::PARAM_INT );

					$dialplan_stmt->execute();

					if ( !isset( $dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'] )) {

						$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'] = [];
					}

					$exten = [];

					while ( $dialplan_row = $dialplan_stmt->fetch( \PDO::FETCH_ASSOC )) {

						$dparam_stmt->bindParam( ':dialplan_id', $dialplan_row['dialplan_id'], \PDO::PARAM_INT );

						$dparam_stmt->execute();

						$dparams = implode( ',', $dparam_stmt->fetchAll( \PDO::FETCH_COLUMN ));

						$exten[] = [
							'cmd'   => $dialplan_row['cmd'],
							'param' => $dparams
						];
					}

					$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'][ $extension_row['ext'] ] = $exten;
				}
			}

		// Get rpoint
			$rpoint_query = <<<SQL
  SELECT *
    FROM client_rpoint
   WHERE rpoint_id = :rpoint_id
SQL;
			$rpoint_stmt = dbh()->prepare( $rpoint_query );

			foreach ( $dialplan_payload as $rpoint_id => $rpoint_payload ) {

				$rpoint_stmt->bindParam( ':rpoint_id', $rpoint_id, \PDO::PARAM_INT );

				$rpoint_stmt->execute();

				$rpoint_row = $rpoint_stmt->fetch( \PDO::PARAM_INT );

				$url_parts = parse_url( $rpoint_row['api_endpoint'] );

				if ( isset( $url_parts['port'] )) {

					$port = $url_parts['port'];
				} else {

					$port = $config_server['api']['config']['port'];
				}

				$result = \pbx_api(
					'Extensions',
					[
						'protocol' => $url_parts['scheme'],
						'server'   => $url_parts['host'],
						'port'     => $port,
						'version'  => $config_server['api']['config']['version']
					],
					$rpoint_row['api_user'],
					$rpoint_row['api_password'],
					$rpoint_payload
				);

//error_log( print_r( $result, TRUE ));
				if ( !$result['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'extension_not_saved', [ 'error' => 'API error: ' . $result['data'] ] );
				}
			}

			$pdo->commit();

			return build_result( TRUE, 'extension_saved', [ 'extension_id' => $params['extension_id'] ] );
		}
	}

?>
