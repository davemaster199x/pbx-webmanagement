<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_users = new jsonrpc\method( 'user.get' );
	$get_users->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_users->param( 'hash',      $_SESSION['user']['hash'] );
	$get_users->param( 'user_id',   $_SESSION['user']['user_id'] );
	$get_users->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_users );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_users->id ]['status'] ) {
		$user = $result[ $get_users->id ]['data']['users'][0];

		if ( empty( $user['clients'] )) {
		// User has no access to any clients, logout
			header( 'Location: /logout.php' );
			exit();
		}
	}

	$ajax_ext = array();

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>

<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
