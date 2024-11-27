<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_speeddial = new jsonrpc\method( 'speeddial.delete' );
	$save_speeddial->param( 'api_token',    $jsonrpc['api_token'] );
	$save_speeddial->param( 'hash',         $_SESSION['user']['hash'] );
	$save_speeddial->param( 'client_id',    $_GET['client_id'] );
	$save_speeddial->param( 'speeddial_id', $_GET['speeddial_id'] );
	$save_speeddial->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_speeddial );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_speeddial->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_speeddial->id ]['message'];
	}

	header( 'Location: /speeddial' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
