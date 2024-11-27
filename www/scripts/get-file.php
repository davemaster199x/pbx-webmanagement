<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$file_get = new jsonrpc\method( 'file.file_get' );
	$file_get->id = $jsonrpc_client->generate_unique_id();
	$file_get->param( [
		'api_token' => $jsonrpc['api_token'],
		'hash'      => $_SESSION['user']['hash'],
		'file_id'   => $_GET['file_id']
	] );

	$jsonrpc_client->method( $file_get );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $file_get->id ]['status'] ) {

		$data      = $result[ $file_get->id ]['data'];
		$file_data = base64_decode( $data['file_data'] );

		if ( substr( $data['mime'], 0, strlen( 'image/jpeg' )) == 'image/jpeg' ) {

			$name = "photo-{$_GET['file_id']}.jpg";
		}

		header( "Content-type: {$data['mime']}" );

		if ( $_GET['disposition'] == 'download' ) {

			header( "Content-disposition: attachment; filename=\"$name\"" );
		} elseif ( $_GET['disposition'] == 'inline' ) {

			header( 'Content-disposition: inline' );
		}

		header( 'Content-length: ' . strlen( $file_data ));

		echo $file_data;
	}

?>
