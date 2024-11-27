<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_user = new jsonrpc\method( 'user.save' );
	$save_user->param( 'api_token',  $config_client['jsonrpc']['api_token'] );
	$save_user->param( 'hash',       $_SESSION['user']['hash'] );
	$save_user->param( 'first_name', $_POST['first_name'] );
	$save_user->param( 'last_name',  $_POST['last_name'] );
	$save_user->param( 'is_global',   ( isset( $_POST['is_global'] ) ? 1 : 0 ));
	$save_user->param( 'email',      $_POST['email'] );
	$save_user->param( 'password',   $_POST['password'] );
	$save_user->param( 'active',     ( isset( $_POST['active'] ) ? 1 : 0 ));
	$save_user->param( 'clients',    ( isset( $_POST['clients'] ) ? $_POST['clients'] : '' ));

	if ( $security['global'] ) {

		$save_user->param( 'is_global',  ( isset( $_POST['is_global'] ) ? 1 : 0 ));
	}

	if ( !empty( $_POST['user_id'] )) {

		$save_user->param( 'user_id', $_POST['user_id'] );
	}

	$save_user->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_user );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_user->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_user->id ]['message'];
	}

	header( 'Location: /users' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
