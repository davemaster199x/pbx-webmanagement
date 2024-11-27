<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['location_id'] )) {

		$get_location = new jsonrpc\method( 'location.get' );
		$get_location->param( 'api_token', 	 $config_client['jsonrpc']['api_token'] );
		$get_location->param( 'hash',      	 $_SESSION['user']['hash'] );
		$get_location->param( 'location_id', $_GET['location_id'] );
		$get_location->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_location );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_location->id ]['status'] ) {

			$location_result = $result[ $get_location->id ]['data']['location'][0];
		}
	}

	$get_clients = new jsonrpc\method( 'client.get' );
	$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
	$get_clients->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_clients );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

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
					'selected' => ( isset( $location_result['client_id'] ) ? $location_result['client_id'] : '' )
				]
			];
		}
	}

	array_push( $endpoint_form,
		[
			'type'  => 'text',
			'label' => 'Name',
			'name'  => 'name',
			'class' => 'width-250px',
			'value' => ( isset( $location_result['name'] ) ? $location_result['name'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'CallerID',
			'name'  => 'callerid',
			'class' => 'width-250px',
			'value' => ( isset( $location_result['callerid'] ) ? $location_result['callerid'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Address',
			'name'  => 'address',
			'class' => 'width-250px',
			'value' => ( isset( $location_result['address'] ) ? $location_result['address'] : '' )
		],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'location_id',
			'value' => ( isset( $_GET['location_id'] ) ? $_GET['location_id'] : '' )
		]
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	$( document ).ready( function() {

	} );

</script>
<form action="/locations/addedit-save.php" method="post">
	<?= form_display( $endpoint_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
