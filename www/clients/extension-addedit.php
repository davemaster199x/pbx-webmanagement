<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	if ( isset( $_GET['extension_id'] )) {
		$jsonrpc_client = new jsonrpc\client();
		$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

		$get_extensions = new jsonrpc\method( 'client.get_extension' );
		$get_extensions->param( 'api_token',    $jsonrpc['api_token'] );
		$get_extensions->param( 'hash',         $_SESSION['user']['hash'] );
		$get_extensions->param( 'extension_id', $_GET['extension_id'] );
		$get_extensions->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_extensions );

		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_extensions->id ]['status'] ) {
			$extension = $result[ $get_extensions->id ]['data']['extensions'][0];
		}
	}

	$client_form = array(
		array(
			'type'  => 'text',
			'label' => 'Extension',
			'name'  => 'ext',
			'class' => 'width-250px',
			'value' => ( isset( $extension['ext'] ) ? $extension['ext'] : '' )
		),
		array(
			'type'  => 'text',
			'label' => 'Context',
			'name'  => 'context',
			'class' => 'width-250px',
			'value' => ( isset( $extension['context'] ) ? $extension['context'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'extension_id',
			'value' => ( isset( $extension['extension_id'] ) ? $extension['extension_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $_GET['client_id'] ) ? $_GET['client_id'] : '' )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/clients/extension-addedit-save.php" method="post">
	<?= form_display( $client_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
