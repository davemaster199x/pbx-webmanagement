<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	error_log( 'CID Device: ' . $_GET['device'] );

    $device = substr( $_GET['device'], 6, 12 );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_cid = new jsonrpc\method( 'api.cid' );
	$get_cid->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_cid->param( 'device',    $device );
	$get_cid->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_cid );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_cid->id ]['status'] ) {

		$cid = $result[ $get_cid->id ]['data']['callerid'];
	} else {

		$cid = 'not_found';
	}

	header( 'Content-type: text/plain' );

	echo $cid;

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
