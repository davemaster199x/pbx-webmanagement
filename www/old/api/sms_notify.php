<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['client_id'] ) || !isset( $_GET['mailbox'] )) {
		echo 'Invalid call.';

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

// Get routes set up for the client
	$get_mailbox = new jsonrpc\method( 'voicemail.sms_notify' );
	$get_mailbox->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_mailbox->param( 'client_id', $_GET['client_id'] );
	$get_mailbox->param( 'mailbox',   $_GET['mailbox'] );
	$get_mailbox->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_mailbox );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_mailbox->id ]['status'] ) {
		$mailbox = $result[ $get_mailbox->id ]['data']['mailbox'];
	}

	echo preg_replace( '/[^0-9]/', '', $mailbox['sms_notify'] );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
