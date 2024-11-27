<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_voicemail = new jsonrpc\method( 'voicemail.save' );
	$save_voicemail->param( 'api_token',    $jsonrpc['api_token'] );
	$save_voicemail->param( 'hash',         $_SESSION['user']['hash'] );
	$save_voicemail->param( 'voicemail_id', $_POST['voicemail_id'] );
	$save_voicemail->param( 'sms_notify',   $_POST['sms_notify'] );
	$save_voicemail->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_voicemail );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_voicemail->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_voicemail->id ]['message'];
	}

	header( 'Location: /voicemail' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
