<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['time_group_id'] )) {
	// Get mailbox that we're editing
		$get_time_group = new jsonrpc\method( 'time_group.get' );
		$get_time_group->param( 'api_token',    $jsonrpc['api_token'] );
		$get_time_group->param( 'hash',         $_SESSION['user']['hash'] );
		$get_time_group->param( 'time_group_id', $_GET['time_group_id'] );
		$get_time_group->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_time_group );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_time_group->id ]['status'] ) {
			$time_group = $result[ $get_time_group->id ]['data']['time_group'][0];
		}
	}

	$time_group_form = array(
		array(
			'type'  => 'text',
			'label' => 'Name',
			'name'  => 'name',
			'value' => ( !empty( $time_group['name'] ) ? $time_group['name'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'time_group_id',
			'value' => ( !empty( $time_group['time_group_id'] ) ? $time_group['time_group_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $time_group['client_id'] ) ? $time_group['client_id'] : $_GET['client_id'] )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/time_group/addedit-save.php" method="post">
	<?= form_display( $time_group_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
