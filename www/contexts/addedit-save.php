<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$children = $_POST['children'] ?? '';

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_context = new jsonrpc\method( 'context.save' );
	$save_context->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$save_context->param( 'hash',        $_SESSION['user']['hash'] );
	$save_context->param( 'rpoint_id',   $_POST['rpoint_id'] );
	$save_context->param( 'children',    $children );
	$save_context->param( 'context',     $_POST['context'] );

	if ( !empty( $_POST['context_id'] )) {

		$save_context->param( 'context_id', $_POST['context_id'] );
	}

	$save_context->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_context );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_context->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_context->id ]['message'];
	}

	header( 'Location: /contexts' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
