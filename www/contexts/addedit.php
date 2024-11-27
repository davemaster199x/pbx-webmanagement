<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['context_id'] )) {

		$get_context = new jsonrpc\method( 'context.get' );
		$get_context->param( 'api_token', 	$config_client['jsonrpc']['api_token'] );
		$get_context->param( 'hash',      	$_SESSION['user']['hash'] );
		$get_context->param( 'context_id',  $_GET['context_id'] );
		$get_context->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_context );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_context->id ]['status'] ) {

			$context_result = $result[ $get_context->id ]['data']['context'][0];
		}

		$get_context_child = new jsonrpc\method( 'context_children.get' );
		$get_context_child->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
		$get_context_child->param( 'hash',        $_SESSION['user']['hash'] );
		$get_context_child->param( 'context_id',  $_GET['context_id'] );
		$get_context_child->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_context_child );
		$jsonrpc_client->send();

		$result_child = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result_child[ $get_context_child->id ]['status'] ) {
			
			$context_children = $result_child[ $get_context_child->id ]['data']['context_children'];
		}
	}

	$get_clients = new jsonrpc\method( 'client.get' );
	$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
	$get_clients->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_clients );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_clients->id ]['status'] ) {

		$client_selectlist = [
			[
				'value'   => '',
				'display' => '[Select]'
			]
		];

		if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) {

			$endpoint_form = [
				[
					'type'  => 'hidden',
					'name'  => 'client_id',
					'value' => $result[ $get_clients->id ]['data']['clients'][0]['client_id']
				]
			];
		} else {

			foreach ( $result[ $get_clients->id ]['data']['clients'] as $client ) {

				$client_selectlist[] = [
					'value'   => $client['client_id'],
					'display' => $client['name']
				];
			}

			$endpoint_form = [
				[
					'type'     => 'select',
					'label'    => 'Client',
					'name'     => 'client_id',
					'class'    => 'width-250px',
					'options'  => $client_selectlist,
					'selected' => ( isset( $context_result['client_id'] ) ? $context_result['client_id'] : '' ),
					'data' => [
						[
							'name'  => 'rpoint_id',
							'value' => isset( $context_result['rpoint_id'] ) ? $context_result['rpoint_id'] : ''
						]
					]
				]
			];
		}
	}

	array_push( $endpoint_form,
		[
			'type'    => 'select',
			'label'   => 'Registration Point',
			'name'    => 'rpoint_id',
			'class'   => 'width-250px',
			'options' => []
		],
		[
			'type'  => 'text',
			'label' => 'Context',
			'name'  => 'context',
			'class' => 'width-250px',
			'value' => ( isset( $context_result['context'] ) ? $context_result['context'] : '' )
		],
		[
			'type'  => 'container',
			'data'  => [
				[
					'name'  => 'function',
					'value' => 'children'
				]
			]
		],
		[
			'type'    => 'container',
			'id'      => 'parent_container',
			'class'   => 'display: inline-block'
		],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'context_id',
			'value' => ( isset( $_GET['context_id'] ) ? $_GET['context_id'] : '' )
		]
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	function get_rpoints( client_id, rpoint_id ) {
	/**
	 * Retrieve rpoints for a selected client.
	 *
	 * @param client_id int   - The client for which to get rpoints.
	 * @param rpoint_id int - The existing rpoint to set, if any.
	 *
	 * @return void
	 */

		$.get( '/ajax/get_rpoints.php?client_id=' + client_id, function( data ) {

		// Clear out all options
			$( 'select[name=rpoint_id]' ).empty();

		// Iterate over each item in the data
			$.each( data, function( index, item ) {

			// Create a new option element
				if ( typeof rpoint_id !== 'undefined' && item.value == rpoint_id ) {

					var option = new Option( item.display, item.value, true, true );
				} else {

					var option = new Option( item.display, item.value );
				}

			// Append the option to the select element
				$( 'select[name=rpoint_id]' ).append( option );
			} );
		} );
	}

	function rpoint_id( rpoint_id, context_id ) {
	/**
	 * Retrieve rpoints for a selected client.
	 *
	 * @param rpoint_id int - The existing rpoint to set, if any.
	 *
	 * @return void
	 */
		$.post( '/ajax/get_children.php', {
			rpoint_id : rpoint_id,
			context_id: context_id
		}, function( data ) {
			$( '[data-function=children]' ).html( data.elements );
		});
	}

	$( document ).ready( function() {

		<?php if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) : ?>

			get_rpoints( <?= $result[ $get_clients->id ]['data']['clients'][0]['client_id'] ?>, <?= $context_result['rpoint_id']; ?> );
		<?php else : ?>

			$( 'select[name=client_id]' ).on( 'change', function() {

				get_rpoints( $( this ).val() );
			} );

			$('select[name=client_id]').each(function() {

				get_rpoints( $( this ).val(), $( this ).data( 'rpoint_id' ) );
			});
		<?php endif; ?>
		
		$( 'select[name=rpoint_id]' ).on( 'change', function() {

			rpoint_id( $( this ).val(), <?= $_GET['context_id'] ?? '' ?> );
		} );

		setTimeout( function() {
			$('select[name=rpoint_id]').each(function() {

				rpoint_id( $( this ).val(), <?= $_GET['context_id'] ?? '' ?> );
			});
		}, 1000 );

		$( 'select[name=child_id]' ).on( 'change', function() {

			var selectedOption = $( this ).find( 'option:selected' );
			var selectedId     = selectedOption.val();
			var selectedName   = selectedOption.text();

			if ( $( '#selected_names_list' ).length === 0) {

				var ulElement = $( '<ul>', { id: 'selected_names_list' } );
				$( '#parent_container' ).append( ulElement );
			}

			if ($( '#selected_names_list' ).find( '[name="children[]"][value="' + selectedId + '"]' ).length === 0) {

				var liElement = $( '<li>', { 'data-id': selectedId } );
				var spanElement = $( '<span>', { text: selectedName } );
				var removeButton = $( '<button>', { text: 'X', class: 'remove-child' } );

				liElement.append( spanElement );
				liElement.append( removeButton );
				liElement.append( '<input type="hidden" name="children[]" value="' + selectedId + '">');
				$('#selected_names_list' ).append( liElement );
			}
		});

		// Function to remove the selected child
		$( document ).on( 'click', '.remove-child', function() {

			var listItem = $( this ).closest( 'li' );
			var selectedId = listItem.data( 'id' );
			listItem.remove();
		});
	} );

</script>
<style>
    li {

        margin-bottom: 5px;
    }

    .remove-child {

        margin-left: 10px;
    }

	#parent_container {

		margin-left: 160px
	}
</style>
<form action="/contexts/addedit-save.php" method="post">
	<?= form_display( $endpoint_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
