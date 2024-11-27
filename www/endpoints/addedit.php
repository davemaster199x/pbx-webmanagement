<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['endpoint_id'] )) {

		$get_endpoints = new jsonrpc\method( 'endpoint.get' );
		$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
		$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
		$get_endpoints->param( 'endpoint_id',   $_GET['endpoint_id'] );
		$get_endpoints->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_endpoints );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_endpoints->id ]['status'] ) {

			$endpoint = $result[ $get_endpoints->id ]['data']['endpoint'][0];
		}
	}

	$rpoint_id = $endpoint['rpoint_id'] ?? '';

	$get_mailbox = new jsonrpc\method( 'voicemail.get' );
	$get_mailbox->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_mailbox->param( 'hash',      $_SESSION['user']['hash'] );
	$get_mailbox->param( 'rpoint_id', $rpoint_id );
	$get_mailbox->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_mailbox );
	$jsonrpc_client->send();

	$result_mailbox = jsonrpc\client::parse_result( $jsonrpc_client->result );

	$mailbox_selectlist = [
		[
			'value'   => '',
			'display' => '[Select]'
		]
	];

	foreach ( $result_mailbox[ $get_mailbox->id ]['data']['voicemail'] as $mailbox ) {

		$mailbox_selectlist[] = [
			'value'   => $mailbox['mailbox'],
			'display' => $mailbox['mailbox']
		];
	}

	$get_clients = new jsonrpc\method( 'client.get' );
	$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
	$get_clients->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_clients );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	$get_device_type = new jsonrpc\method( 'device_type.get' );
	$get_device_type->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_device_type->param( 'hash',      $_SESSION['user']['hash'] );
	$get_device_type->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_device_type );
	$jsonrpc_client->send();

	$result_device_type = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_clients->id ]['status'] ) {

		$client_selectlist = [
			[
				'value'   => '',
				'display' => '[Select]'
			]
		];

		if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) {

			$endpoint_form = [
				[
					'type'  => 'hidden',
					'name'  => 'client_id',
					'value' => $result[ $get_clients->id ]['data']['clients'][0]['client_id']
				]
			];
		} else {

			foreach ( $result[ $get_clients->id ]['data']['clients'] as $client ) {

				$client_selectlist[] = [
					'value'   => $client['client_id'],
					'display' => $client['name']
				];
			}

			$endpoint_form = [
				[
					'type'     => 'select',
					'label'    => 'Client',
					'name'     => 'client_id',
					'class'    => 'width-250px',
					'options'  => $client_selectlist,
					'selected' => ( isset( $endpoint['client_id'] ) ? $endpoint['client_id'] : '' ),
					'data' => [
						[
							'name'  => 'rpoint_id',
							'value' => isset( $endpoint['rpoint_id'] ) ? $endpoint['rpoint_id'] : ''
						],
						[
							'name'  => 'location_id',
							'value' => isset( $endpoint['location_id'] ) ? $endpoint['location_id'] : ''
						]
					]
				]
			];
		}
	}

	if ( $result_device_type[ $get_device_type->id ]['status'] ) {

		$device_typelist = [];

		foreach ( $result_device_type[ $get_device_type->id ]['data']['device_type'] as $device ) {

			$device_typelist[] = [
				'value'   => $device['type_id'],
				'display' => $device['name']
			];
		}
	}

	$caller_id = $endpoint['callerid'] ?? '';

	if ( $caller_id  ) {
		
		preg_match( '/<(.*?)>/', $caller_id, $match );

		$callerid_name = trim( substr( $caller_id, 0, strpos( $caller_id, '<' )));
	}

	array_push( $endpoint_form,
		[
			'type'    => 'select',
			'label'   => 'Registration Point',
			'name'    => 'rpoint_id',
			'class'   => 'width-250px',
			'options' => []
		],
		[
			'type'    => 'select',
			'label'   => 'Location',
			'name'    => 'location_id',
			'class'   => 'width-250px',
			'options' => []
		],
		[
			'type'     => 'select',
			'label'    => 'Device Type',
			'name'     => 'device_type_id',
			'class'    => 'width-250px',
			'options'  => $device_typelist,
			'selected' => ( isset( $endpoint['device_type_id'] ) ? $endpoint['device_type_id'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Label',
			'name'  => 'label',
			'class' => 'width-250px',
			'value' => ( isset( $endpoint['label'] ) ? $endpoint['label'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Name',
			'name'  => 'name',
			'class' => 'width-250px',
			'value' => ( isset( $endpoint['name'] ) ? $endpoint['name'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Password',
			'name'  => 'password',
			'class' => 'width-250px',
			'value' => ( isset( $endpoint['password'] ) ? $endpoint['password'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Context',
			'name'  => 'context',
			'class' => 'width-250px',
			'value' => ( isset( $endpoint['context'] ) ? $endpoint['context'] : '' )
		],
		[
			'type'  => 'select',
			'label' => 'Transport',
			'name'  => 'transport',
			'class' => 'width-250px',
			'selected' => ( isset( $endpoint['transport'] ) ? $endpoint['transport'] : '' ),
			'options'  => [
				[
					'value'   => 'transport-udp',
					'display' => 'UDP'
				],
				[
					'value'   => 'transport-tcp',
					'display' => 'TCP'
				],
				[
					'value'   => 'transport-tls',
					'display' => 'TLS'
				],

			]
		],
		[
			'type'  => 'text',
			'label' => 'Caller ID Name',
			'name'  => 'callerid_name',
			'class' => 'width-250px',
			'value' => ( isset( $callerid_name ) ? $callerid_name : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Caller ID Number',
			'name'  => 'callerid_number',
			'class' => 'width-250px',
			'value' => ( isset( $match[1] ) ? $match[1] : '' )
		],
		[
			'type'  => 'select',
			'label' => 'Mailboxes',
			'name'  => 'mailboxes',
			'class' => 'width-250px',
			'selected' => ( isset( $endpoint['mailboxes'] ) ? $endpoint['mailboxes'] : '' ),
			'options'  => $mailbox_selectlist
		],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'endpoint_id',
			'value' => ( isset( $_GET['endpoint_id'] ) ? $_GET['endpoint_id'] : '' )
		]
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	function get_rpoints( client_id, rpoint_id ) {
	/**
	 * Retrieve rpoints for a selected client.
	 *
	 * @param client_id int   - The client for which to get rpoints.
	 * @param rpoint_id int - The existing rpoint to set, if any.
	 *
	 * @return void
	 */

		$.get( '/ajax/get_rpoints.php?client_id=' + client_id, function( data ) {

		// Clear out all options
			$( 'select[name=rpoint_id]' ).empty();

		// Iterate over each item in the data
			$.each( data, function( index, item ) {

			// Create a new option element
				if ( typeof rpoint_id !== 'undefined' && item.value == rpoint_id ) {

					var option = new Option( item.display, item.value, true, true );
				} else {

					var option = new Option( item.display, item.value );
				}

			// Append the option to the select element
				$( 'select[name=rpoint_id]' ).append( option );
			} );
		} );
	}

	function get_locations( client_id, location_id ) {
	/**
	 * Retrieve locations for a selected client.
	 *
	 * @param client_id int   - The client for which to get locations.
	 * @param location_id int - The existing location to set, if any.
	 *
	 * @return void
	 */

		$.get( '/ajax/get_locations.php?client_id=' + client_id, function( data ) {

		// Clear out all options
			$( 'select[name=location_id]' ).empty();

		// Iterate over each item in the data
			$.each( data, function( index, item ) {

			// Create a new option element
				if ( typeof location_id !== 'undefined' && item.value == location_id ) {

					var option = new Option( item.display, item.value, true, true );
				} else {

					var option = new Option( item.display, item.value );
				}

			// Append the option to the select element
				$( 'select[name=location_id]' ).append( option );
			} );
		} );
	}

	$( document ).ready( function() {

		<?php if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) : ?>

			get_rpoints( <?= $result[ $get_clients->id ]['data']['clients'][0]['client_id'] ?>, <?= $endpoint['rpoint_id']; ?> );
			get_locations( <?= $result[ $get_clients->id ]['data']['clients'][0]['client_id'] ?>, <?= $endpoint['location_id']; ?> );
		<?php else : ?>

			$( 'select[name=client_id]' ).on( 'change', function() {

				get_rpoints( $( this ).val() );
				get_locations( $( this ).val() );
			} );

			$( 'select[name=client_id]' ).each( function() {

				get_rpoints( $( this ).val(), $( this ).data( 'rpoint_id' ) );
				get_locations( $( this ).val(), $( this ).data( 'location_id' ) );
			});
		<?php endif; ?>
	} );

</script>
<form action="/endpoints/addedit-save.php" method="post">
	<?= form_display( $endpoint_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
