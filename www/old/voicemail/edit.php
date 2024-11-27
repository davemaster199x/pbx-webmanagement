<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

// Get mailbox that we're editing
	$get_voicemail = new jsonrpc\method( 'voicemail.get' );
	$get_voicemail->param( 'api_token',    $jsonrpc['api_token'] );
	$get_voicemail->param( 'hash',         $_SESSION['user']['hash'] );
	$get_voicemail->param( 'voicemail_id', $_GET['voicemail_id'] );
	$get_voicemail->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_voicemail );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_voicemail->id ]['status'] ) {
		$voicemail = $result[ $get_voicemail->id ]['data']['voicemail'][0];
	}

	$voicemail_form = array(
		array(
			'type'  => 'text',
			'label' => 'SMS Notify',
			'name'  => 'sms_notify',
			'value' => ( !empty( $voicemail['sms_notify'] ) ? $voicemail['sms_notify'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'voicemail_id',
			'value' => $voicemail['voicemail_id']
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => $voicemail['client_id']
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/voicemail/edit-save.php" method="post">
	<h2>Mailbox: <?= $voicemail['mailbox']; ?></h2>
	<?= form_display( $voicemail_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
