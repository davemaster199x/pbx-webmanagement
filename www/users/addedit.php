<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() || ( !$security['global'] && !$security[''] )) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['user_id'] )) {

		$get_users = new jsonrpc\method( 'user.get' );
		$get_users->param( 'api_token', $config_client['jsonrpc']['api_token'] );
		$get_users->param( 'hash',      $_SESSION['user']['hash'] );
		$get_users->param( 'user_id',   $_GET['user_id'] );
		$get_users->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_users );
	}

	$get_clients = new jsonrpc\method( 'client.get' );
	$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
	$get_clients->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_clients );

	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( isset( $get_users )) {

		if ( $result[ $get_users->id ]['status'] ) {

			$user = $result[ $get_users->id ]['data']['users'][0];
		}
	}

	if ( $result[ $get_clients->id ]['status'] ) {

		$client_checkboxes = [];

		foreach ( $result[ $get_clients->id ]['data']['clients'] as $client ) {

			$client_checkboxes[] = [
				'display' => $client['name'],
				'name'    => 'clients[' . $client['client_id'] . ']',
				'value'   => '1',
				'checked' => ( !empty( $user['clients'] && in_array( $client['client_id'], $user['clients'] )) ? TRUE : FALSE )
			];
		}
	}

	$user_form = [
		[
			'type'  => 'text',
			'label' => 'First Name',
			'name'  => 'first_name',
			'class' => 'width-250px',
			'value' => ( isset( $user['first_name'] ) ? $user['first_name'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Last Name',
			'name'  => 'last_name',
			'class' => 'width-250px',
			'value' => ( isset( $user['last_name'] ) ? $user['last_name'] : '' )
		],
		[
			'type'    => 'checkbox',
			'label'   => 'Permissions',
			'options' => [
				( $security['global'] ?
					[
						'display' => 'Global Admin',
						'name'    => 'is_global',
						'value'   => '1',
						'checked' => ( !empty( $user['is_global'] ) ? TRUE : FALSE )
					] : ''
				)
			]
		],
		[
			'type'  => 'text',
			'label' => 'Email Address',
			'name'  => 'email',
			'class' => 'width-250px',
			'value' => ( isset( $user['email'] ) ? $user['email'] : '' )
		],
		[
			'type'  => 'password',
			'label' => 'Password',
			'name'  => 'password',
			'class' => 'width-250px',
			'value' => ''
		],
		[
			'type'    => 'checkbox',
			'label'   => 'Clients',
			'options' => $client_checkboxes
		],
		[
			'type'    => 'checkbox',
			'label'   => '',
			'options' => [
				[
					'display' => 'Active',
					'name'    => 'active',
					'value'   => '1',
					'checked' => ( !empty( $user['active'] ) ? TRUE : FALSE )
				]
			]
		],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'user_id',
			'value' => ( isset( $user['user_id'] ) ? $user['user_id'] : '' )
		]
	];

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/users/addedit-save.php" method="post">
	<?= form_display( $user_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
