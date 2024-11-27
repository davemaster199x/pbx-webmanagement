<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	if ( isset( $_GET['client_id'] )) {

		$jsonrpc_client = new jsonrpc\client();
		$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

		$get_clients = new jsonrpc\method( 'client.get' );
		$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
		$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
		$get_clients->param( 'client_id', $_GET['client_id'] );
		$get_clients->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_clients );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_clients->id ]['status'] ) {

			$client = $result[ $get_clients->id ]['data']['clients'][0];
		}
	}

	$client_form = [
		[
			'type'  => 'text',
			'label' => 'Client',
			'name'  => 'name',
			'class' => 'width-250px',
			'value' => ( isset( $client['name'] ) ? $client['name'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'HTTP Username',
			'name'  => 'http_username',
			'class' => 'width-250px',
			'value' => ( isset( $client['http_username'] ) ? $client['http_username'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'HTTP Password',
			'name'  => 'http_password',
			'class' => 'width-250px',
			'value' => ( isset( $client['http_password'] ) ? $client['http_password'] : '' )
		],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $client['client_id'] ) ? $client['client_id'] : '' )
		]
	];

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/clients/addedit-save.php" method="post">
	<?= form_display( $client_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
