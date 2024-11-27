<?php

    include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

    $reorder_param = new jsonrpc\method( 'extension_dialplan.reorder_command' );
    $reorder_param->param( 'api_token',        $config_client['jsonrpc']['api_token'] );
    $reorder_param->param( 'hash',             $_SESSION['user']['hash'] );
    $reorder_param->param( 'dialplan_id',      $_GET['dialplan_id'] );
    $reorder_param->param( 'swapCommandId',    $_GET['swapCommandId'] );
    $reorder_param->param( 'swapCommandOrder', $_GET['swapCommandOrder'] );
    $reorder_param->param( 'currentId',        $_GET['currentId'] );
    $reorder_param->param( 'currentOrder',     $_GET['currentOrder'] );
    $reorder_param->id = $jsonrpc_client->generate_unique_id();

    $jsonrpc_client->method( $reorder_param );
    $jsonrpc_client->send();

    $result = jsonrpc\client::parse_result( $jsonrpc_client->result );

    if ( $result[ $reorder_param->id ]['status'] ) {
        
        echo 'success';
    }
?>