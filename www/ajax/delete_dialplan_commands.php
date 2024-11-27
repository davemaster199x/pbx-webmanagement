<?php

    include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

    $delete_param = new jsonrpc\method( 'extension_dialplan.delete_command' );
    $delete_param->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
    $delete_param->param( 'hash',        $_SESSION['user']['hash'] );
    $delete_param->param( 'dialplan_id', $_GET['dialplan_id'] );
    $delete_param->id = $jsonrpc_client->generate_unique_id();

    $jsonrpc_client->method( $delete_param );
    $jsonrpc_client->send();

    $result = jsonrpc\client::parse_result( $jsonrpc_client->result );

    if ( $result[ $delete_param->id ]['status'] ) {
        
        echo 'success';
    }
?>