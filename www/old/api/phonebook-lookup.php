<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['client_id'] ) && !isset( $_GET['phonebook_id'] ) && !isset( $_GET['token'] )) {

		exit();
	}

	if ( $_GET['token'] != 'QmBgQRgP8Ni7BVHe' ) {

		exit();
	}

	header( 'Content-type: text/plain' );

	if ( empty( $_GET['number'] )) {

		echo '0';

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_entries = new jsonrpc\method( 'phonebook.get_phonebook' );
	$get_entries->param( 'api_token',    $jsonrpc['api_token'] );
	$get_entries->param( 'client_id',    $_GET['client_id'] );
	$get_entries->param( 'phonebook_id', $_GET['phonebook_id'] );
	$get_entries->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entries );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_entries->id ]['status'] ) {
		$entries = $result[ $get_entries->id ]['data']['entry'];

		foreach ( $entries as $entry ) {
			$number = preg_replace( '/[^0-9]/', '', $entry['number'] );

			if ( $number == $_GET['number'] ) {

				echo '1';

				$match = TRUE;
			}
		}
	}

	if ( empty( $match )) {

		echo '0';
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
