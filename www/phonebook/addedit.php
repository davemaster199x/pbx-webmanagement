<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['phonebook_id'] )) {

		$get_phonebook = new jsonrpc\method( 'phonebook.get' );
		$get_phonebook->param( 'api_token', 	 $config_client['jsonrpc']['api_token'] );
		$get_phonebook->param( 'hash',      	 $_SESSION['user']['hash'] );
		$get_phonebook->param( 'phonebook_id',   $_GET['phonebook_id'] );
		$get_phonebook->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_phonebook );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_phonebook->id ]['status'] ) {

			$phonebook = $result[ $get_phonebook->id ]['data']['phonebook'][0];
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

			$phonebook_form = [
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

			$phonebook_form = [
				[
					'type'     => 'select',
					'label'    => 'Client',
					'name'     => 'client_id',
					'class'    => 'width-250px',
					'options'  => $client_selectlist,
					'selected' => ( isset( $phonebook['client_id'] ) ? $phonebook['client_id'] : '' )
				]
			];
		}
	}

	array_push( $phonebook_form,
        [
            'type'  => 'text',
            'label' => 'Name',
            'name'  => 'name',
            'class' => 'width-250px',
            'value' => ( isset( $phonebook['name'] ) ? $phonebook['name'] : '' )
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
        ]
    );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	$( document ).ready( function() {

	} );

</script>
<form action="/phonebook/addedit-save.php" method="post">
	<?= form_display( $phonebook_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
