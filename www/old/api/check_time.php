<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['time_group_id'] ) && !isset( $_GET['token'] )) {

		exit();
	}

	if ( $_GET['token'] != 'QmBgQRgP8Ni7BVHe' ) {

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_entries = new jsonrpc\method( 'time_group.check_time' );
	$get_entries->param( 'api_token',     $jsonrpc['api_token'] );
	$get_entries->param( 'time_group_id', $_GET['time_group_id'] );
	$get_entries->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entries );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	header( 'Content-type: text/plain' );

	if ( $result[ $get_entries->id ]['status'] ) {

		if ( $result[ $get_entries->id ]['data'] ) {

			echo '1';
		} else {

			echo '0';
		}
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
