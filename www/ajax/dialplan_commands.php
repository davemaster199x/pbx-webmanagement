<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	header( 'Content-type: application/json' );

	if ( !isset( $_GET['command'] )) {

		echo json_encode( [ 'elements' => '' ] );

		exit();
	}
	
	$show_param = false;

	if ( isset( $_GET['dialplan_id'] )) {

		$show_param = true;

		$get_param = new jsonrpc\method( 'extension_dialplan_param.get' );
		$get_param->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
		$get_param->param( 'hash',        $_SESSION['user']['hash'] );
		$get_param->param( 'dialplan_id', $_GET['dialplan_id'] );
		$get_param->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_param );
		$jsonrpc_client->send();

		$resultparam = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $resultparam[ $get_param->id ]['status'] ) {

			$param = ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][0]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][0]['param'] : '' );
			$prio  = ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][1]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][1]['param'] : '' );
		}
	}

	switch ( $_GET['command'] ) {

		case 'Answer' :

			echo json_encode( [ 'elements' => '<em>No parameters</em>' ] );

		break;

		case 'Background' :

			$elements = [
				[
					'type'     => 'text',
					'label'    => 'Sound File',
					'name'     => 'sound_file_bg' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'Dial' :

			$get_endpoints = new jsonrpc\method( 'endpoint.get' );
			$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
			$get_endpoints->param( 'client_id', $_GET['client_id'] );
			$get_endpoints->param( 'rpoint_id', $_GET['rpoint_id'] );
			$get_endpoints->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_endpoints );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_endpoints->id ]['status'] ) {

				$endpoint_list = [];

				foreach ( $result[ $get_endpoints->id ]['data']['endpoint'] as $endpoint ) {

					$endpoint_list[] = [
						'value'   => $endpoint['endpoint_name'],
						'display' => $endpoint['label'] . ' (' . $endpoint['endpoint_name'] . ')'
					];
				}
			}

			$elements = [];
			$addendpoint = ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 );

			$elements[] = [
				'type'  => 'button',
				'name'  => 'add_endpoint',
				'value' => 'Add Endpoint',
				'data' => [
					[
						'name'  => 'id',
						'value' => ( isset( $_GET['dialplan_id'] ) ? $_GET['dialplan_id'] : 0 )
					],
					[
						'name'  => 'addcount',
						'value' =>  ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 )
					],
				]
			];
			
			if ( $show_param ) {
			
				if ( $resultparam[ $get_param->id ]['status'] ) {

					$parts = explode( '&', $param );

					foreach ( $parts as $index => $part ) {

						$subparts = explode( '/', $part );
						$value    = end( $subparts );

						if ( strpos( $value, ',' ) !== false ) {

							$value = strstr( $value, ',', true );
						}

						$get_endpoint = strrpos( $value, "_" );
						$endpoint     = substr( $value, $get_endpoint + 1 );

							$elements[] = [
								'type'     => 'select',
								'label'    => 'Endpoint',
								'name'     => 'endpoint' . $_GET['index'] . '[]',
								'class'    => 'endpoint_' . $_GET['index'] . $index,
								'options'  => $endpoint_list,
								'selected' => ( isset( $endpoint ) ? $endpoint : '' )
									
							];
						
						$parts      = explode( "_", $value );
						$ring_delay = ( isset( $parts[1] ) ?  $parts[1] : '' );

						if (  $ring_delay == '00' ) {

							$ring_delay = '0';
						} elseif( preg_match( '/^0[1-9]$/', $ring_delay ) ) {
							
							$ring_delay = substr( $ring_delay, 1 );
						}

							$elements[] = [
								'type'     => 'text',
								'label'    => 'Ring Delay',
								'name'     => 'ringdelay' . $_GET['index'] . '[]',
								'class'    => 'width-100px endpoint_' . $_GET['index'] . $index,
								'value'    => ( isset( $ring_delay ) ? $ring_delay : '' ),
								'data'  => [
									[
										'name'  => 'function',
										'value' => 'ringdelay-input'
									],
								]
							];

							$elements[] = [
								'type'  => 'button',
								'name'  => '',
								'value' => 'X',
								'class' => 'endpoint_' . $_GET['index'] . $index,
								'data'  => [
									[
										'name'  => 'function',
										'value' => 'remove-endpoint'
									],
									[
										'name'  => 'endpoint',
										'value' => $_GET['index'] . $index
									]
								]
							];
					}
				}
			}

			$elements[] = [
				'type'  => 'container',
				'class' => 'sub-endpoint',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'parameters-dial'
					],
					[
						'name'  => 'index',
						'value' => $_GET['index']
					]
				]
			];

			$dial_input = $prio;

			$elements[] = [
					'type'     => 'text',
					'label'    => 'Options',
					'name'     => 'dial_input' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $dial_input ) ? $dial_input : '' )
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );
			
		break;

		case 'Dial_add' :
			
			$get_endpoints = new jsonrpc\method( 'endpoint.get' );
			$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
			$get_endpoints->param( 'client_id', $_GET['client_id'] );
			$get_endpoints->param( 'rpoint_id', $_GET['rpoint_id'] );
			$get_endpoints->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_endpoints );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_endpoints->id ]['status'] ) {

				$endpoint_list = [];

				foreach ( $result[ $get_endpoints->id ]['data']['endpoint'] as $endpoint ) {

					$endpoint_list[] = [
						'value'   => $endpoint['endpoint_name'],
						'display' => $endpoint['label'] . ' (' . $endpoint['endpoint_name'] . ')'
					];
				}
			}

			$elements = [];

			$addendpoint = ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 );

			$count = $addendpoint + 100;
			$elements[] = [
				'type'     => 'select',
				'label'    => 'Endpoint',
				'name'     => 'endpoint' . $_GET['index'] . '[]',
				'class'    => 'width-100px endpoint_' . $_GET['index'] . $count,
				'options'  => $endpoint_list,
				'selected' => '',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'dial-endpoint'
					],
				]
			];

			$elements[] = [
				'type'     => 'text',
				'label'    => 'Ring Delay',
				'name'     => 'ringdelay'.$_GET['index'].'[]',
				'class'    => 'width-100px endpoint_' . $_GET['index'] . $count,
				'value'    => '',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'ringdelay-input'
					],
				]
			];

			$elements[] = [
				'type'  => 'button',
				'name'  => '',
				'value' => 'X',
				'class' => 'endpoint_' . $_GET['index'] . $count,
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'remove-endpoint'
					],
					[
						'name'  => 'endpoint',
						'value' => $_GET['index'] . $count
					]
				]
			];
			
			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'Goto' :

			$get_extensions = new jsonrpc\method( 'extension.get' );
			$get_extensions->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_extensions->param( 'hash',      $_SESSION['user']['hash'] );
			$get_extensions->param( 'client_id', $_GET['client_id'] );
			$get_extensions->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_extensions );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_extensions->id ]['status'] ) {

				$extension_list = [
					[
						'value'   => '(Same)',
						'display' => '(Same)'
					]
				];

				foreach ( $result[ $get_extensions->id ]['data']['extension'] as $extension ) {

					$extension_list[] = [
						'value'   => $extension['ext'],
						'display' => $extension['ext']
					];
				}
			}

			$elements = [
				[
					'type'     => 'select',
					'label'    => 'Extension',
					'name'     => 'ext_command' . $_GET['index'],
					'class'    => 'width-100px',
					'options'  => $extension_list,
					'selected' => ( isset( $param ) ? $param : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'Priority',
					'name'     => 'prio' . $_GET['index'],
					'class'    => 'width-50px',
					'value'    => ( isset( $prio ) ? $prio : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'GotoIfTime' :

			$elements = [
				[
					'type'     => 'text',
					'label'    => 'Time',
					'name'     => 'goto_time' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][0]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][0]['param'] : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'Day of Week',
					'name'     => 'goto_day_week' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][1]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][1]['param'] : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'Day of Month',
					'name'     => 'goto_day_month' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][2]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][2]['param'] : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'Month',
					'name'     => 'goto_month' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][3]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][3]['param'] : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'If True',
					'name'     => 'goto_true' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][4]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][4]['param'] : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'If False',
					'name'     => 'goto_false' . $_GET['index'],
					'class'    => 'width-100px',
					'value'    => ( isset( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][5]['param'] ) ? $resultparam[ $get_param->id ]['data']['extension_dialplan_param'][5]['param'] : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'HangUp' :

			echo json_encode( [ 'elements' => '<em>No parameters</em>' ] );

		break;

		case 'Log' :

			$loglevel_list = [
				[
					'value'   => 'NOTICE',
					'display' => 'Notice'
				],
				[
					'value'   => 'DEBUG',
					'display' => 'Debug'
				],
				[
					'value'   => 'ERROR',
					'display' => 'Error'
				],
				[
					'value'   => 'VERBOSE',
					'display' => 'Verbose'
				],
				[
					'value'   => 'WARNING',
					'display' => 'Warning'
				]
			];

			$log = $prio;

			$elements = [
				[
					'type'     => 'select',
					'label'    => 'Log Level',
					'name'     => 'log_level' . $_GET['index'],
					'class'    => 'width-100px',
					'options'  => $loglevel_list,
					'selected' => ( isset( $param ) ? $param : '' )
				],
				[
					'type'     => 'text',
					'label'    => 'Log',
					'name'     => 'log' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $log ) ? $log : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'Page' :

			$get_endpoints = new jsonrpc\method( 'endpoint.get' );
			$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
			$get_endpoints->param( 'client_id', $_GET['client_id'] );
			$get_endpoints->param( 'rpoint_id', $_GET['rpoint_id'] );
			$get_endpoints->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_endpoints );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_endpoints->id ]['status'] ) {

				$endpoint_list = [];

				foreach ( $result[ $get_endpoints->id ]['data']['endpoint'] as $endpoint ) {

					$endpoint_list[] = [
						'value'   => $endpoint['endpoint_name'],
						'display' => $endpoint['label'] . ' (' . $endpoint['endpoint_name'] . ')'
					];
				}
			}

			$elements = [];
			$addendpoint = ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 );

			$elements[] = [
				'type'  => 'button',
				'name'  => 'add_endpoint_page',
				'value' => 'Add Endpoint',
				'data' => [
					[
						'name'  => 'id',
						'value' => ( isset( $_GET['dialplan_id'] ) ? $_GET['dialplan_id'] : 0 )
					],
					[
						'name'  => 'addcount',
						'value' =>  ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 )
					],
				]
			];
			
			if ( $show_param ) {
			
				if ( $resultparam[ $get_param->id ]['status'] ) {

					$parts      = explode( '&', $param );
					$values     = array();

					foreach ( $parts as $index => $part ) {
					// Split each part by '/'
						$subparts = explode( '/', $part );

					// Extract the value after '/'
						$value = end( $subparts );
						
					// Check if the value contains a comma and exclude everything after it
						if ( strpos( $value, ',' ) !== false ) {

							$value = strstr( $value, ',', true );
						}
						
						$elements[] = [
							'type'     => 'select',
							'label'    => 'Endpoint',
							'name'     => 'endpoint_page' . $_GET['index'] . '[]',
							'class'    => 'width-100px endpoint_' . $_GET['index'] . $index,
							'options'  => $endpoint_list,
							'selected' => ( isset( $value ) ? $value : '' )
								
						];

						$elements[] = [
								'type'  => 'button',
								'name'  => '',
								'value' => 'X',
								'class' => 'endpoint_' . $_GET['index'] . $index,
								'data'  => [
									[
										'name'  => 'function',
										'value' => 'remove-endpoint'
									],
									[
										'name'  => 'endpoint',
										'value' => $_GET['index'] . $index
									]
								]
							];
					}
				}
			}

			$elements[] = [
				'type'  => 'container',
				'class' => 'sub-endpoint',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'parameters-page'
					],
					[
						'name'  => 'index',
						'value' => $_GET['index']
					]
				]
			];

			$page_input = $prio;

			$elements[] = [
					'type'     => 'text',
					'label'    => 'Options',
					'name'     => 'page_input' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $page_input ) ? $page_input : '' )
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );
			
		break;

		case 'Page_add' :

			$get_endpoints = new jsonrpc\method( 'endpoint.get' );
			$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
			$get_endpoints->param( 'client_id', $_GET['client_id'] );
			$get_endpoints->param( 'rpoint_id', $_GET['rpoint_id'] );
			$get_endpoints->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_endpoints );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_endpoints->id ]['status'] ) {

				$endpoint_list = [];

				foreach ( $result[ $get_endpoints->id ]['data']['endpoint'] as $endpoint ) {

					$endpoint_list[] = [
						'value'   => $endpoint['endpoint_name'],
						'display' => $endpoint['label'] . ' (' . $endpoint['endpoint_name'] . ')'
					];
				}
			}

			$elements = [];
			$addendpoint = ( isset( $_GET['addendpoint'] ) ? $_GET['addendpoint'] : 0 );

			$count = $addendpoint + 100;

			$elements[] = [
				'type'     => 'select',
				'label'    => 'Endpoint',
				'name'     => 'endpoint_page' . $_GET['index'] . '[]',
				'class'    => 'width-100px endpoint_' . $_GET['index'] . $count,
				'options'  => $endpoint_list,
				'selected' => ''
			];

			$elements[] = [
				'type'  => 'button',
				'name'  => '',
				'value' => 'X',
				'class' => 'endpoint_' . $_GET['index'] . $count,
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'remove-endpoint'
					],
					[
						'name'  => 'endpoint',
						'value' => $_GET['index'] . $count
					]
				]
			];
			
			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );
		break;

		case 'PlayBack' :

			$elements = [
				[
					'type'     => 'text',
					'label'    => 'Sound File',
					'name'     => 'sound_file' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'Set' :

			$elements = [
				[
					'type'     => 'text',
					'label'    => 'Variable',
					'name'     => 'variable' . $_GET['index'],
					'class'    => 'width-200px',
					'value'    => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'VoiceMail' :

			$get_voicemails = new jsonrpc\method( 'voicemail.get' );
			$get_voicemails->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_voicemails->param( 'hash',      $_SESSION['user']['hash'] );
			$get_voicemails->param( 'client_id', $_GET['client_id'] );
			$get_voicemails->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_voicemails );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_voicemails->id ]['status'] ) {

				$voicemail_list = [];

				foreach ( $result[ $get_voicemails->id ]['data']['voicemail'] as $voicemail ) {

					$voicemail_list[] = [
						'value'   => $voicemail['mailbox'],
						'display' => $voicemail['mailbox']
					];
				}
			}

			$elements = [
				[
					'type'     => 'select',
					'label'    => 'Voicemail',
					'name'     => 'mailbox' . $_GET['index'],
					'class'    => 'width-50px',
					'options'  => $voicemail_list,
					'selected' => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'VoiceMailMain' :

			$get_voicemails = new jsonrpc\method( 'voicemail.get' );
			$get_voicemails->param( 'api_token', $config_client['jsonrpc']['api_token'] );
			$get_voicemails->param( 'hash',      $_SESSION['user']['hash'] );
			$get_voicemails->param( 'client_id', $_GET['client_id'] );
			$get_voicemails->param( 'rpoint_id', $_GET['rpoint_id'] );
			$get_voicemails->id = $jsonrpc_client->generate_unique_id();

			$jsonrpc_client->method( $get_voicemails );
			$jsonrpc_client->send();

			$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

			if ( $result[ $get_voicemails->id ]['status'] ) {

				$voicemail_list = [];

				$voicemail_list[] = [
						'value'   => '',
						'display' => '(None)'
					];
				foreach ( $result[ $get_voicemails->id ]['data']['voicemail'] as $voicemail ) {

					$voicemail_list[] = [
						'value'   => $voicemail['mailbox'],
						'display' => $voicemail['mailbox']
					];
				}
			}

			$elements = [
				[
					'type'     => 'select',
					'label'    => 'Mailbox',
					'name'     => 'voice_mailbox' . $_GET['index'],
					'class'    => 'width-100px',
					'options'  => $voicemail_list,
					'selected' => ( isset( $param ) ? $param : '' )
				],
				[
					'type'    => 'text',
					'label'   => 'Options',
					'name'    => 'voice_mailbox_text' . $_GET['index'],
					'class'   => 'width-50px',
					'value'   => ( isset( $prio ) ? $prio : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'Wait' :

			$elements = [
				[
					'type'    => 'text',
					'label'   => 'Seconds',
					'name'    => 'seconds' . $_GET['index'],
					'class'   => 'width-50px',
					'value'   => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		case 'WaitExten' :

			$elements = [
				[
					'type'    => 'text',
					'label'   => 'Seconds',
					'name'    => 'seconds_exten' . $_GET['index'],
					'class'   => 'width-50px',
					'value'   => ( isset( $param ) ? $param : '' )
				]
			];

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;

		default :
			
			$elements = [];

			if ( $resultparam[ $get_param->id ]['status'] ) {

				foreach ( $resultparam[ $get_param->id ]['data']['extension_dialplan_param'] as $dialparam ) {

					$elements[] = [
					'type'    => 'text',
					'label'   => $_GET['command'],
					'name'    => $_GET['command'] . '' . $_GET['index'] . '[]',
					'class'   => 'width-100px',
					'value'   => $dialparam['param']
					];
				}
			}

			echo json_encode( [
				'elements' => form_display( $elements, $form_templates['main_form'] )
			] );

		break;
	}

?>
