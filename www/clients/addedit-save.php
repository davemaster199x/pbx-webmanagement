<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_client = new jsonrpc\method( 'client.save' );
	$save_client->param( 'api_token',     $config_client['jsonrpc']['api_token'] );
	$save_client->param( 'hash',          $_SESSION['user']['hash'] );
	$save_client->param( 'name',          $_POST['name'] );
	$save_client->param( 'http_username', $_POST['http_username'] );
	$save_client->param( 'http_password', $_POST['http_password'] );

	if ( !empty( $_POST['client_id'] )) {

		$save_client->param( 'client_id', $_POST['client_id'] );
	}

	$save_client->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_client );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_client->id ]['status'] ) {

		header( 'Location: /clients' );
	} else {
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
