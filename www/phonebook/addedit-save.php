<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_phonebook = new jsonrpc\method( 'phonebook.save' );
	$save_phonebook->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$save_phonebook->param( 'hash',        $_SESSION['user']['hash'] );
	$save_phonebook->param( 'client_id',   $_POST['client_id'] );
	$save_phonebook->param( 'name',        $_POST['name'] );

	if ( !empty( $_POST['phonebook_id'] )) {

		$save_phonebook->param( 'phonebook_id', $_POST['phonebook_id'] );
	}

	$save_phonebook->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_phonebook );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_phonebook->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_phonebook->id ]['message'];
	}

	header( 'Location: /phonebook' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
