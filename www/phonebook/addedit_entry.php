<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['entry_id'] )) {

		$get_entry = new jsonrpc\method( 'phonebook.get_entry' );
		$get_entry->param( 'api_token', 	 $config_client['jsonrpc']['api_token'] );
		$get_entry->param( 'hash',      	 $_SESSION['user']['hash'] );
		$get_entry->param( 'phonebook_id',   $_GET['phonebook_id'] );
		$get_entry->param( 'entry_id',       $_GET['entry_id'] );
		$get_entry->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_entry );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_entry->id ]['status'] ) {

			$entries = $result[ $get_entry->id ]['data']['entries'][0];
		}
	}

    $phonebook_form = [
        [
            'type'  => 'text',
            'label' => 'First Name',
            'name'  => 'first_name',
            'class' => 'width-250px',
            'value' => ( isset( $entries['first_name'] ) ? $entries['first_name'] : '' )
        ],
        [
            'type'  => 'text',
            'label' => 'Last Name',
            'name'  => 'last_name',
            'class' => 'width-250px',
            'value' => ( isset( $entries['last_name'] ) ? $entries['last_name'] : '' )
        ],
        [
            'type'  => 'text',
            'label' => 'Number',
            'name'  => 'number',
            'class' => 'width-250px',
            'value' => ( isset( $entries['number'] ) ? $entries['number'] : '' )
        ],
        [
            'type'  => 'select',
            'label' => 'Type',
            'name'  => 'type',
            'class' => 'width-250px',
            'selected' => ( isset( $entries['type'] ) ? $entries['type'] : '' ),
            'options'  => [
                [
                    'value'   => 'Work',
					'display' => 'Work'
                ],
                [
                    'value'   => 'Home',
					'display' => 'Home'
                ],
                [
                    'value'   => 'Mobile',
					'display' => 'Mobile'
                ]
            ]
        ],
        [
            'type'  => 'submit',
            'name'  => 'save',
            'value' => 'Save'
        ],
        [
            'type'  => 'hidden',
            'name'  => 'phonebook_id',
            'value' => ( isset( $_GET['phonebook_id'] ) ? $_GET['phonebook_id'] : '' )
        ],
        [
            'type'  => 'hidden',
            'name'  => 'entry_id',
            'value' => ( isset( $_GET['entry_id'] ) ? $_GET['entry_id'] : '' )
        ]
    ];

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	$( document ).ready( function() {

	} );

</script>
<form action="/phonebook/addedit_entry-save.php" method="post">
	<?= form_display( $phonebook_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
