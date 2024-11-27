<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['voicemail_id'] )) {

		$get_voicemails = new jsonrpc\method( 'voicemail.get' );
		$get_voicemails->param( 'api_token', 	$config_client['jsonrpc']['api_token'] );
		$get_voicemails->param( 'hash',      	$_SESSION['user']['hash'] );
		$get_voicemails->param( 'voicemail_id', $_GET['voicemail_id'] );
		$get_voicemails->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_voicemails );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_voicemails->id ]['status'] ) {

			$voicemail = $result[ $get_voicemails->id ]['data']['voicemail'][0];
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
					'selected' => ( isset( $voicemail['client_id'] ) ? $voicemail['client_id'] : '' ),
					'data' => [
						[
							'name'  => 'rpoint_id',
							'value' => $voicemail['rpoint_id']
						]
					]
				]
			];
		}
	}

	if ( isset( $voicemail['options'] ) ) {
	// Split the options string into an array based on the delimiter "|"
		$options_array = explode( '|', $voicemail['options'] );

	// Initialize variables to store option names and values
		$attach = $saycid = $delete = $sayduration = '';

	// Loop through each option in the array
		foreach ( $options_array as $option ) {
		// Split each option into name and value based on the "=" delimiter
			list( $name, $value ) = explode( '=', $option );
			
		// Assign the value to the corresponding variable based on the name
			if ( $name === 'attach' ) {
				$attach = $value;
			} elseif ( $name === 'saycid' ) {
				$saycid = $value;
			} elseif ( $name === 'delete' ) {
				$delete = $value;
			} elseif ( $name === 'sayduration' ) {
				$sayduration = $value;
			}
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
			'label' => 'Mailbox',
			'name'  => 'mailbox',
			'class' => 'width-250px',
			'value' => ( isset( $voicemail['mailbox'] ) ? $voicemail['mailbox'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Password',
			'name'  => 'password',
			'class' => 'width-250px',
			'value' => ( isset( $voicemail['password'] ) ? $voicemail['password'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Name',
			'name'  => 'name',
			'class' => 'width-250px',
			'value' => ( isset( $voicemail['name'] ) ? $voicemail['name'] : '' )
		],
		[
			'type'  => 'text',
			'label' => 'Email',
			'name'  => 'email',
			'class' => 'width-250px',
			'value' => ( isset( $voicemail['email'] ) ? $voicemail['email'] : '' )
		],
		[
            'type'     => 'radio',
            'label'    => 'Attach',
            'class'    => 'width-300px',
            'name'     => 'attach',
            'options'  =>  [
                [
                    'display' => 'Yes',
                    'value'   => 'yes'
                ],
                [
                    'display' => 'No',
                    'value'   => 'no'
                ]
            ],
            'selected' => ( isset( $attach ) ? $attach : '' )
        ],
		[
            'type'     => 'radio',
            'label'    => 'Saycid',
            'class'    => 'width-300px',
            'name'     => 'saycid',
            'options'  =>  [
                [
                    'display' => 'Yes',
                    'value'   => 'yes'
                ],
                [
                    'display' => 'No',
                    'value'   => 'no'
                ]
            ],
            'selected' => ( isset( $saycid ) ? $saycid : '' )
        ],
		[
            'type'     => 'radio',
            'label'    => 'Sayduration',
            'class'    => 'width-300px',
            'name'     => 'sayduration',
            'options'  =>  [
                [
                    'display' => 'Yes',
                    'value'   => 'yes'
                ],
                [
                    'display' => 'No',
                    'value'   => 'no'
                ]
            ],
            'selected' => ( isset( $sayduration ) ? $sayduration : '' )
        ],
		[
            'type'     => 'radio',
            'label'    => 'Delete',
            'class'    => 'width-300px',
            'name'     => 'delete',
            'options'  =>  [
                [
                    'display' => 'Yes',
                    'value'   => 'yes'
                ],
                [
                    'display' => 'No',
                    'value'   => 'no'
                ]
            ],
            'selected' => ( isset( $delete ) ? $delete : '' )
        ],
		[
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		],
		[
			'type'  => 'hidden',
			'name'  => 'voicemail_id',
			'value' => ( isset( $_GET['voicemail_id'] ) ? $_GET['voicemail_id'] : '' )
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

	$( document ).ready( function() {

		<?php if ( count( $result[ $get_clients->id ]['data']['clients'] ) == 1 ) : ?>

			get_rpoints( <?= $result[ $get_clients->id ]['data']['clients'][0]['client_id'] ?>, <?= $voicemail['rpoint_id']; ?> );
		<?php else : ?>

			$( 'select[name=client_id]' ).on( 'change', function() {

				get_rpoints( $( this ).val() );
			} );

			$('select[name=client_id]').each(function() {

				get_rpoints( $( this ).val(), $( this ).data( 'rpoint_id' ) );
			});
		<?php endif; ?>
	} );

</script>
<form action="/voicemails/addedit-save.php" method="post">
	<?= form_display( $endpoint_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
