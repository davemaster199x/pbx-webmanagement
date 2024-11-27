<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$login = new jsonrpc\method( 'user.login' );
	$login->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$login->param( 'username',  $_POST['username'] );
	$login->param( 'password',  $_POST['password'] );
	$login->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $login );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $login->id ]['status'] ) {
	// Login successful, store session and redirect to main page

		$_SESSION['user'] = $result[ $login->id ]['data']['user'];

		if ( isset( $_POST['return'] )) {
			header( "Location: {$_POST['return']}" );
		} else {
			header( 'Location: /dashboard' );
		}
	} else {

		if ( isset( $_POST['return'] )) {
			header( 'Location: /?error=bad_authentication&return=' . urlencode( $_POST['return'] ));
		} else {
			header( 'Location: /?error=bad_authentication' );
		}
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
