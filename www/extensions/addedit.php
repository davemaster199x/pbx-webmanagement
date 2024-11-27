<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$total_command = 0;

	if ( isset( $_GET['extension_id'] )) {

		$get_extensions = new jsonrpc\method( 'extension.get' );
		$get_extensions->param( 'api_token', 	$config_client['jsonrpc']['api_token'] );
		$get_extensions->param( 'hash',      	$_SESSION['user']['hash'] );
		$get_extensions->param( 'extension_id', $_GET['extension_id'] );
		$get_extensions->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_extensions );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_extensions->id ]['status'] ) {

			$extension = $result[ $get_extensions->id ]['data']['extension'][0];
		}

		$get_extensions_dialplan = new jsonrpc\method( 'extension_dialplan.get' );
		$get_extensions_dialplan->param( 'api_token', 	$config_client['jsonrpc']['api_token'] );
		$get_extensions_dialplan->param( 'hash',      	$_SESSION['user']['hash'] );
		$get_extensions_dialplan->param( 'extension_id',$_GET['extension_id'] );
		$get_extensions_dialplan->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_extensions_dialplan );
		$jsonrpc_client->send();

		$result_dialplan = jsonrpc\client::parse_result( $jsonrpc_client->result );

		$dialplan_form = [];

		$dialplan_form = [
			[
				'type'  => 'button',
				'name'  => '',
				'value' => 'Add Command',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'add-command'
					]
				]
			]
		];

		$index               = 0;
		$parameters          = 0;
		$count_command_up    = 1;
		$count_command_down  = 1;
		$countcommand        = 1;
		$swap                = 1;
		$swapup              = 1;
		$swapdown            = 1;
		$current             = 1;

		foreach ( $result_dialplan[ $get_extensions_dialplan->id ]['data']['extensions_dialplan'] as $dialplan ) {

			$dialplan_form[] = [
				'type'     => 'button',
				'label'    => '',
				'name'     => 'swap_command',
				'class'    => 'swap-commandsUp' . $count_command_up++,
				'value' => '▲',
				'data' => [
					[
						'name'  => 'function',
						'value' => 'swap-commandsUp'
					],
					[
						'name'  => 'swap',
						'value' => $swapup++
					],
					[
						'name'  => 'current'.$current,
						'value' => $dialplan['dialplan_id'] . ',' . $dialplan['prio']
					],
				],
			];

			$dialplan_form[] = [
				'type'     => 'select',
				'label'    => 'Command #' . $countcommand++,
				'name'     => 'cmd[]',
				'class'    => 'width-100px swap-class'.$swap++,
				'selected' => $dialplan['cmd'],
				'options'  => [
					[
						'display' => 'Answer',
						'value'   => 'Answer'
					],
					[
						'display' => 'Background',
						'value'   => 'Background'
					],
					[
						'display' => 'Dial',
						'value'   => 'Dial'
					],
					[
						'display' => 'Goto',
						'value'   => 'Goto',
					],
					[
						'display' => 'GotoIfTime',
						'value'   => 'GotoIfTime',
					],
					[
						'display' => 'Log',
						'value'   => 'Log'
					],
					[
						'display' => 'HangUp',
						'value'   => 'HangUp'
					],
					[
						'display' => 'Page',
						'value'   => 'Page'
					],
					[
						'display' => 'PlayBack',
						'value'   => 'PlayBack'
					],
					[
						'display' => 'Set',
						'value'   => 'Set'
					],
					[
						'display' => 'VoiceMail',
						'value'   => 'VoiceMail'
					],
					[
						'display' => 'VoiceMailMain',
						'value'   => 'VoiceMailMain'
					],
					[
						'display' => 'Wait',
						'value'   => 'Wait'
					],
					[
						'display' => 'WaitExten',
						'value'   => 'WaitExten'
					],
					
				],
				'data' => [
					[
						'name'  => 'index',
						'value' => $index++
					],
					[
						'name'  => 'id',
						'value' => $dialplan['dialplan_id']
					],
					[
						'name'  => 'cmd',
						'value' => $dialplan['cmd']
					]
				],
				
			];

			if ( !in_array( $dialplan['cmd'], [ 'Answer', 'Dial', 'Goto', 'HangUp', 'Log', 'Page', 'PlayBack', 'Set', 'VoiceMail', 'Wait', 'WaitExten', 'VoiceMailMain', 'GotoIfTime' ] )) {

				$dialplan_form[ count( $dialplan_form ) - 1 ]['options'][] = [
					'display' => $dialplan['cmd'],
					'value'   => $dialplan['cmd']
				];
			}

			$dialplan_form[] = [
				'type'  => 'button',
				'name'  => '',
				'value' => 'X',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'delete_commands'
					],
					[
						'name'  => 'dialplan_id',
						'value' => $dialplan['dialplan_id']
					]
				]
			];

			$dialplan_form[] = [
				'type'  => 'container',
				'class' => 'sub',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'parameters'
					],
					[
						'name'  => 'index',
						'value' => $parameters++
					]
				]
			];

			$dialplan_form[] = [
				'type'     => 'button',
				'label'    => '',
				'name'     => 'swap_command',
				'class'    => 'swap-commands' . $count_command_down++,
				'value' => '▼',
				'data' => [
					[
						'name'  => 'function',
						'value' => 'swap-commandsDown'
					],
					[
						'name'  => 'swap',
						'value' => $swapdown++
					],
					[
						'name'  => 'current'.$current,
						'value' => $dialplan['dialplan_id'] . ',' . $dialplan['prio']
					],
				],
			];

			$dialplan_form[] = [
				'type'     => 'hr'
			];

			$total_command += 1;
			$current++;
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

			$extension_form = [
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

			$extension_form = [
				[
					'type'     => 'select',
					'label'    => 'Client',
					'name'     => 'client_id',
					'class'    => 'width-250px',
					'options'  => $client_selectlist,
					'selected' => ( isset( $extension['client_id'] ) ? $extension['client_id'] : '' ),
					'data' => [
						[
							'name'  => 'rpoint_id',
							'value' => $extension['rpoint_id'] ?? ''
						]
					]
				]
			];
		}
	}

	if ( isset( $_GET['extension_id'] )) {

		array_push( $extension_form,
			[
				'type'    => 'select',
				'label'   => 'Registration Point',
				'name'    => 'rpoint_id',
				'class'   => 'width-250px',
				'options' => [],
				'data' => [
					[
						'name'  => 'context_id',
						'value' => $extension['context_id']
					]
				]
			],
			[
				'type'    => 'select',
				'label'   => 'Context',
				'name'    => 'context_id',
				'class'   => 'width-250px',
				'options' => []
			],
			[
				'type'  => 'text',
				'label' => 'Extension',
				'name'  => 'ext',
				'class' => 'width-250px',
				'value' => ( isset( $extension['ext'] ) ? $extension['ext'] : '' )
			],
			[
				'type'  => 'submit',
				'name'  => 'save',
				'value' => 'Save'
			],
			[
				'type'  => 'button',
				'name'  => '',
				'class'   => 'margin-left-159px',
				'value' => 'Copy to New Extension',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'copy_extension'
					],
					[
						'name'  => 'extension_id',
						'value' => ( isset( $_GET['extension_id'] ) ? $_GET['extension_id'] : '' )
					]
				]
			],
			[
				'type'  => 'hidden',
				'name'  => 'extension_id',
				'value' => ( isset( $_GET['extension_id'] ) ? $_GET['extension_id'] : '' )
			]
		);
	} else {
		
		array_push( $extension_form,
			[
				'type'    => 'select',
				'label'   => 'Registration Point',
				'name'    => 'rpoint_id',
				'class'   => 'width-250px',
				'options' => [],
				'data' => [
					[
						'name'  => 'context_id',
						'value' => $extension['context_id'] ?? ''
					]
				]
			],
			[
				'type'    => 'select',
				'label'   => 'Context',
				'name'    => 'context_id',
				'class'   => 'width-250px',
				'options' => []
			],
			[
				'type'  => 'text',
				'label' => 'Extension',
				'name'  => 'ext',
				'class' => 'width-250px',
				'value' => ( isset( $extension['ext'] ) ? $extension['ext'] : '' )
			],
			[
				'type'  => 'submit',
				'name'  => 'save',
				'value' => 'Save'
			],
			[
				'type'  => 'hidden',
				'name'  => 'extension_id',
				'value' => ( isset( $_GET['extension_id'] ) ? $_GET['extension_id'] : '' )
			]
		);
	}
	

	$command_element = [];

	if ( isset( $extension['extension_id'] )) {

		$command_element = [
			[
				'type'    => 'select',
				'label'   => 'Command',
				'name'    => 'cmd[]',
				'class'   => 'width-100px',
				'options' => [
					[
						'display' => 'Answer',
						'value'   => 'Answer'
					],
					[
						'display' => 'Background',
						'value'   => 'Background'
					],
					[
						'display' => 'Dial',
						'value'   => 'Dial'
					],
					[
						'display' => 'Goto',
						'value'   => 'Goto'
					],
					[
						'display' => 'HangUp',
						'value'   => 'HangUp'
					],
					[
						'display' => 'Log',
						'value'   => 'Log'
					],
					[
						'display' => 'Page',
						'value'   => 'Page'
					],
					[
						'display' => 'PlayBack',
						'value'   => 'PlayBack'
					],
					[
						'display' => 'Set',
						'value'   => 'Set'
					],
					[
						'display' => 'VoiceMail',
						'value'   => 'VoiceMail'
					],
					[
						'display' => 'Wait',
						'value'   => 'Wait'
					],
				],
				'data' => [
					[
						'name'  => 'index',
						'value' => '%INDEX%'
					]
				]
			],
			[
				'type'  => 'button',
				'name'  => '',
				'value' => 'X',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'remove_commands'
					],
					[
						'name'  => 'index',
						'value' => '%INDEX%'
					]
				]
			],
			[
				'type'  => 'container',
				'class' => 'sub',
				'data'  => [
					[
						'name'  => 'function',
						'value' => 'parameters'
					],
					[
						'name'  => 'index',
						'value' => '%INDEX%'
					]
				]
			]
		];
	}
?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	function add_command( total_command ) {
	/**
	 * Adds a command row.
	 *
	 * @return void
	 */

		var command_form = `<?= str_replace( "\n", '', form_display( $command_element, $form_templates['main_form'] )); ?>`;

	// Check to see if the counter has been initialized
		if ( typeof add_command.counter == 'undefined' ) {

			add_command.counter = 0;
		}

		add_command.counter += total_command;

		$( '[data-function=commands]' ).append( command_form.replace( /%INDEX%/g, add_command.counter ));

		$( '[data-function=commands] select[data-index=' + add_command.counter + ']' ).change();

		++add_command.counter;

		var form     = $( '#save-commands' );
		var formData = form.serialize();

		$.ajax({
			url: form.attr( 'action' ),
			type: 'POST',
			data: formData,
			success: function(response) {

				location.reload();
			}
		});
	}

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

	function get_context( rpoint_id, context_id ) {
	/**
	 * Retrieve context for a selected client.
	 *
	 * @param rpoint_id int - The existing rpoint_id to set, if any.
	 *
	 * @return void
	 */

		$.get( '/ajax/get_context.php?rpoint_id=' + rpoint_id, function( data ) {

		// Clear out all options
			$( 'select[name=context_id]' ).empty();

		// Iterate over each item in the data
			$.each( data, function( index, item ) {

			// Create a new option element
				if ( typeof context_id !== 'undefined' && item.value == context_id ) {

					var option = new Option( item.display, item.value, true, true );
				} else {

					var option = new Option( item.display, item.value );
				}

			// Append the option to the select element
				$( 'select[name=context_id]' ).append( option );
			} );
		} );
	}

	$( document ).ready( function() {

		<?php if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) : ?>

			get_rpoints( <?= $result[ $get_clients->id ]['data']['clients'][0]['client_id'] ?>, <?= $extension['rpoint_id']; ?> );
		<?php else : ?>

			$( 'select[name=client_id]' ).on( 'change', function() {

				get_rpoints( $( this ).val() );
			} );

			$( 'select[name=rpoint_id]' ).on( 'change', function() {

				get_context( $( this ).val() );
			} );

			$('select[name=client_id]').each(function() {

				get_rpoints( $( this ).val(), $( this ).data( 'rpoint_id' ) );
			});

			setTimeout(function() {

				$('select[name=rpoint_id]').each(function() {
					get_context( $( this ).val(), $( this ).data( 'context_id' ) );
				});
			}, 1000);
		<?php endif; ?>

		<?php if ( isset( $extension['extension_id'] )) : ?>

			$( '[type=button][data-function=add-command]' ).on( 'click', function() {

				add_command( <?=$total_command;?> );
			} );

			$( document ).on( 'change', '[data-function=commands] select', function() {

				var select = this;

				$.get( '/ajax/dialplan_commands.php?command=' + $( select ).val() + '&client_id='+ <?= $extension['client_id'] ?> + '&index=' + $( select ).data( 'index' ) + '&dialplan_id=' + $( select ).data('id') + '&rpoint_id=' + <?= $extension['rpoint_id'] ?>, function( data ) {

					$( '[data-function=parameters][data-index=' + $( select ).data( 'index' ) + ']' ).html( data.elements );
				} );
			} );

		// For the deleting of commands
			$( '[type=button][data-function=delete_commands]' ).on( 'click', function() {

				var select = this;

				$.get( '/ajax/delete_dialplan_commands.php?dialplan_id=' + $( select ).data('dialplan_id'), function( data ) {

					if ( data == 'success' ) {
						
						location.reload();
					} else {

						alert( 'Something went wrong!' );
					}
				} );
			} );

		// For copying extension
			$( '[type=button][data-function=copy_extension]' ).on( 'click', function() {

				var select       = this;
				var extension_id = $( select ).data('extension_id');
    
			// Prompt the user to enter a new extension name
				var new_extension_name = prompt( "Enter the new extension name:" );
				
				if ( new_extension_name !== null ) {

					$.get( '/ajax/copy_extension.php?extension_id=' + extension_id + '&new_extension_name=' + new_extension_name, function( data ) {

						if ( data == 'success' ) {
							
							window.location.href = '/extensions';
						} else {

							alert( 'Something went wrong!' );
						}
					} );
				} else {
					alert( "No extension name entered." );
				}
			} );
		
		// For the deleting of endpoints
			$( document ).on( 'click', '[type=button][data-function=remove-endpoint]', function() {

				var select              = this;
				var endpoint_class_name = 'endpoint_' + $( select ).data( 'endpoint' );

			// Select all elements with the specified class name
				var elementstoremove = document.querySelectorAll( '.' + endpoint_class_name );

			// Check if any elements exist before attempting to remove them
				if ( elementstoremove.length > 0 ) {
				// Remove the elements one by one
					elementstoremove.forEach( function( element ) {
						element.remove();
					});
				} else {

					console.log("Elements not found");
				}
				
			});
		
		// For the existing commands to be populate
			$( document ).ready( function() {

				$( '[data-function=commands] select[name="cmd[]"]' ).each(function() {
					var select = $( this );

					$.get('/ajax/dialplan_commands.php?command=' + select.val() + '&client_id=' + <?= $extension['client_id'] ?> + '&index=' + select.data('index') + '&dialplan_id=' + select.data('id') + '&cmd=' + select.data('cmd') + '&rpoint_id=' + <?= $extension['rpoint_id'] ?>, function(data) {
						
						$( '[data-function=parameters][data-index=' + select.data('index') + ']' ).html(data.elements);
					});
				});
			});
		
		// For swaping of order number
			$( '[type=button][data-function=swap-commandsUp]' ).on( 'click', function() {

				var select       = $( this );
				var current      = select.data( 'swap' );
				var next         = current - 1;

				var button       = document.querySelector( '.swap-commandsUp' + next );
				var currentData  = button.getAttribute( 'data-current' + next );

				selectedValue    = currentData.split( ',' );
				swapCommandId    = selectedValue[0];
				swapCommandOrder = selectedValue[1];

				currentTemp      = select.data( 'current'+current ).split( ',' );
				currentId        = currentTemp[0];
				currentOrder     = currentTemp[1];

				$.get( '/ajax/reorder_dialplan_commands.php?dialplan_id=' + <?=$_GET['extension_id']?> + '&swapCommandId=' + swapCommandId + '&swapCommandOrder=' + swapCommandOrder + '&currentId=' + currentId + '&currentOrder=' + currentOrder, function( data ) {

					if ( data == 'success' ) {
						
						location.reload();
					} else {

						alert( 'Something went wrong!' );
					}
				} );
			});

			$( '[type=button][data-function=swap-commandsDown]' ).on( 'click', function() {

				var select       = $( this );
				var current      = select.data( 'swap' );
				var next         = current + 1;

				var button       = document.querySelector( '.swap-commandsUp' + next );
				var currentData  = button.getAttribute( 'data-current' + next );

				selectedValue    = currentData.split( ',' );
				swapCommandId    = selectedValue[0];
				swapCommandOrder = selectedValue[1];

				currentTemp      = select.data( 'current'+current ).split( ',' );
				currentId        = currentTemp[0];
				currentOrder     = currentTemp[1];

				$.get( '/ajax/reorder_dialplan_commands.php?dialplan_id=' + <?=$_GET['extension_id']?> + '&swapCommandId=' + swapCommandId + '&swapCommandOrder=' + swapCommandOrder + '&currentId=' + currentId + '&currentOrder=' + currentOrder, function( data ) {

					if ( data == 'success' ) {
						
						location.reload();
					} else {

						alert( 'Something went wrong!' );
					}
				} );
			});

		// For add additional endpoint
			$( document ).on( 'click', '[data-function=parameters] input[name="add_endpoint"]', function() {

				var select            = $( this ).closest( '[data-function=parameters]' );
				var add               = $( this );
				var addendpointbutton = $( '[name=add_endpoint]' );
				var addcount          = addendpointbutton.attr( 'data-addcount' );
				addcount              = parseInt( addcount ) + 1;
				addendpointbutton.attr( 'data-addcount', addcount );
				var addcount          = addendpointbutton.attr( 'data-addcount' );
				
				$.get( '/ajax/dialplan_commands.php?command=Dial_add&client_id='+ <?= $extension['client_id'] ?> + '&index=' + $( select ).data( 'index' ) + '&addendpoint='+ addcount + '&dialplan_id=' + add.data('id') + '&rpoint_id=' + <?= $extension['rpoint_id'] ?>, function( data ) {

					$( '[data-function=parameters-dial][data-index=' + $( select ).data( 'index' ) + ']' ).append( data.elements );
				} );
			});

			$( document ).on( 'click', '[data-function=parameters] input[name="add_endpoint_page"]', function() {

				var select            = $( this ).closest( '[data-function=parameters]' );
				var add               = $( this );
				var addendpointbutton = $( '[name=add_endpoint_page]' );
				var addcount          = addendpointbutton.attr( 'data-addcount' );
				addcount              = parseInt( addcount ) + 1;
				addendpointbutton.attr( 'data-addcount', addcount );
				var addcount          = addendpointbutton.attr( 'data-addcount' );

				$.get( '/ajax/dialplan_commands.php?command=Page_add&client_id='+ <?= $extension['client_id'] ?> + '&index=' + $( select ).data( 'index' ) + '&addendpoint='+ addcount + '&dialplan_id=' + add.data('id') + '&rpoint_id=' + <?= $extension['rpoint_id'] ?>, function( data ) {

					$( '[data-function=parameters-page][data-index=' + $( select ).data( 'index' ) + ']' ).append( data.elements );
				} );
			});
		<?php endif; ?>
	} );

</script>

<style type="text/css">

	.swap-commandsUp1{

		margin-left: 159px
	}

	.margin-left-159px{

		margin-left: 159px;
	}

</style>
<form action="/extensions/addedit-save.php" id="save-commands" method="post">
	<?= form_display( $extension_form, $form_templates['main_form'] ); ?>
	<?php if ( isset( $extension['extension_id'] )) : ?>
		<div class="commands" data-function="commands">
			<?= form_display( $dialplan_form, $form_templates['main_form'] ); ?>
		</div><!-- /.commands -->
	<?php endif; ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
