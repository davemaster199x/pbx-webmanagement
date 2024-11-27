<?php

    include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

    $copy_extension_param = new jsonrpc\method( 'extension.copy_extension' );
    $copy_extension_param->param( 'api_token',          $config_client['jsonrpc']['api_token'] );
    $copy_extension_param->param( 'hash',               $_SESSION['user']['hash'] );
    $copy_extension_param->param( 'extension_id',       $_GET['extension_id'] );
    $copy_extension_param->param( 'new_extension_name', $_GET['new_extension_name'] );
    $copy_extension_param->id = $jsonrpc_client->generate_unique_id();

    $jsonrpc_client->method( $copy_extension_param );
    $jsonrpc_client->send();

    $result = jsonrpc\client::parse_result( $jsonrpc_client->result );
    
    if ( $result[ $copy_extension_param->id ]['status'] ) {
        
        echo 'success';
    }
?>