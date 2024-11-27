<?php

    include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

    header( 'Content-type: application/json' );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

    if ( isset( $_POST['rpoint_id'] ) ) {

        $context_id = $_POST['context_id'] ?? '';

        $get_context = new jsonrpc\method( 'context.get' );
        $get_context->param( 'api_token', 	$config_client['jsonrpc']['api_token'] );
        $get_context->param( 'hash',      	$_SESSION['user']['hash'] );
        $get_context->param( 'context_id',  $context_id );
        $get_context->id = $jsonrpc_client->generate_unique_id();

        $jsonrpc_client->method( $get_context );
        $jsonrpc_client->send();

        $result = jsonrpc\client::parse_result( $jsonrpc_client->result );

        if ( $result[ $get_context->id ]['status'] ) {

            $context_result = $result[ $get_context->id ]['data']['context'][0];
        }

        $context_children = '';
        if ( $context_id ) {
            $get_context_child = new jsonrpc\method( 'context_children.get' );
            $get_context_child->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
            $get_context_child->param( 'hash',        $_SESSION['user']['hash'] );
            $get_context_child->param( 'context_id',  $context_id );
            $get_context_child->id = $jsonrpc_client->generate_unique_id();

            $jsonrpc_client->method( $get_context_child );
            $jsonrpc_client->send();

            $result_child = jsonrpc\client::parse_result( $jsonrpc_client->result );

            if ( $result_child[ $get_context_child->id ]['status'] ) {
                
                $context_children = $result_child[ $get_context_child->id ]['data']['context_children'];
            }
        }
        $elements  = [];
        $rpoint_id = $_POST['rpoint_id'] ?? '';

        if ( $rpoint_id ) {
            $get_context = new jsonrpc\method( 'context.get' );
            $get_context->param( 'api_token', $config_client['jsonrpc']['api_token'] );
            $get_context->param( 'hash',      $_SESSION['user']['hash'] );
            $get_context->param( 'rpoint_id', $rpoint_id );
            $get_context->id = $jsonrpc_client->generate_unique_id();

            $jsonrpc_client->method( $get_context );
            $jsonrpc_client->send();

            $result_client = jsonrpc\client::parse_result( $jsonrpc_client->result );

            if ( $result_client[ $get_context->id ]['status'] ) {

                $contexts = $result_client[ $get_context->id ]['data']['context'];
            } else {

                print_r( $result_client[ $get_context->id ]['message'] );
            }

            $parent_list  = [];

            foreach ( $contexts as $context ) {

                $is_checked = FALSE; // Initialize the checked state

                if ( isset( $_POST['context_id'] )) {

                // Check if the current context ID exists in $context_children['child_id']
                    foreach ( $context_children as $child ) {

                        if ( $child['child_id'] == $context['context_id'] ) {

                            $is_checked = TRUE; // Set checked state to true if it matches
                            break; // Exit the inner loop once matched
                        }
                    }
                }

                $self_context = $_POST['context_id'] ?? '';

            // Dont include self context 
                if ( $self_context != $context['context_id'] ) {
                    
                    $parent_list[] = [
                        'value'   => $context['context_id'],
                        'display' => $context['context'],
                        'name'    => 'children[]',
                        'checked' => $is_checked
                    ];
                }
            }

            $elements = [
                [
                    'type'    => 'checkbox',
                    'label'   => 'Children',
                    'options' => $parent_list
                ]
            ];

            echo json_encode( [
                'elements' => form_display( $elements, $form_templates['main_form'] )
            ] );
        } else {
            echo json_encode( [
                'elements' => form_display( $elements, $form_templates['main_form'] )
            ] );
        }
    }
?>