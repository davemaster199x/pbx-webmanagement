<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$options = '';

	$option_names = ['attach', 'delete', 'saycid', 'sayduration'];

	foreach ( $option_names as $option_name ) {
		if ( isset( $_POST[ $option_name ] )) {
		// Append the option and its value to $options
			$options .= ( $options ? '|' : '' ) . $option_name . '=' . $_POST[ $option_name ];
		}
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_voicemail = new jsonrpc\method( 'voicemail.save' );
	$save_voicemail->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$save_voicemail->param( 'hash',        $_SESSION['user']['hash'] );
	$save_voicemail->param( 'rpoint_id',   $_POST['rpoint_id'] );
	$save_voicemail->param( 'mailbox',     $_POST['mailbox'] );
	$save_voicemail->param( 'password',    $_POST['password'] );
	$save_voicemail->param( 'name',        $_POST['name'] );
	$save_voicemail->param( 'email',       $_POST['email'] );
	$save_voicemail->param( 'options',     $options );

	if ( !empty( $_POST['voicemail_id'] )) {

		$save_voicemail->param( 'voicemail_id', $_POST['voicemail_id'] );
	}

	$save_voicemail->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_voicemail );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_voicemail->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_voicemail->id ]['message'];
	}

	header( 'Location: /voicemails' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
