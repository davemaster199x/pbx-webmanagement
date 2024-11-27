<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_extension = new jsonrpc\method( 'client.save_extension' );
	$save_extension->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$save_extension->param( 'hash',      $_SESSION['user']['hash'] );
	$save_extension->param( 'ext',       $_POST['ext'] );
	$save_extension->param( 'context',   $_POST['context'] );

	if ( !empty( $_POST['client_id'] )) {
		$save_extension->param( 'client_id', $_POST['client_id'] );
	}

	if ( !empty( $_POST['extension_id'] )) {
		$save_extension->param( 'extension_id', $_POST['extension_id'] );
	}

	$save_extension->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_extension );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_extension->id ]['status'] ) {
		header( "Location: /clients/addedit.php?client_id={$_POST['client_id']}" );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
