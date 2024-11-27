<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_status = new jsonrpc\method( 'extension.status' );
	$get_status->param( 'api_token',  $config_client['jsonrpc']['api_token'] );
	$get_status->param( 'hash',       $_SESSION['user']['hash'] );
	$get_status->param( 'user_id',    $_SESSION['user']['user_id'] );
	$get_status->param( 'extensions', explode( '|', $_GET['exts'] ));
	$get_status->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_status );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_status->id ]['status'] ) {
		echo json_encode( $result[ $get_status->id ]['data']['ext_status'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
