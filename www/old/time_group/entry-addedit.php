<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['time_group_id'] ) && isset( $_GET['time_condition_id'] )) {
	// Get mailbox that we're editing
		$get_entries = new jsonrpc\method( 'time_group.get_entries' );
		$get_entries->param( 'api_token',         $jsonrpc['api_token'] );
		$get_entries->param( 'hash',              $_SESSION['user']['hash'] );
		$get_entries->param( 'time_group_id',     $_GET['time_group_id'] );
		$get_entries->param( 'time_condition_id', $_GET['time_condition_id'] );
		$get_entries->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_entries );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_entries->id ]['status'] ) {
			$entry = $result[ $get_entries->id ]['data']['entry'][0];
		}
	}

	$entry_form = array(
		array(
			'type'     => 'select',
			'label'    => 'Start Day',
			'name'     => 'day_start',
			'selected' => ( !empty( $entry['day_start'] ) ? $entry['day_start'] : '' ),
			'options'  => array(
				array(
					'display' => 'Sunday',
					'value'   => '0'
				),
				array(
					'display' => 'Monday',
					'value'   => '1'
				),
				array(
					'display' => 'Tuesday',
					'value'   => '2'
				),
				array(
					'display' => 'Wednesday',
					'value'   => '3'
				),
				array(
					'display' => 'Thursday',
					'value'   => '4'
				),
				array(
					'display' => 'Friday',
					'value'   => '5'
				),
				array(
					'display' => 'Saturday',
					'value'   => '6'
				)
			)
		),
		array(
			'type'     => 'select',
			'label'    => 'End Day',
			'name'     => 'day_end',
			'selected' => ( !empty( $entry['day_end'] ) ? $entry['day_end'] : '' ),
			'options'  => array(
				array(
					'display' => 'Sunday',
					'value'   => '0'
				),
				array(
					'display' => 'Monday',
					'value'   => '1'
				),
				array(
					'display' => 'Tuesday',
					'value'   => '2'
				),
				array(
					'display' => 'Wednesday',
					'value'   => '3'
				),
				array(
					'display' => 'Thursday',
					'value'   => '4'
				),
				array(
					'display' => 'Friday',
					'value'   => '5'
				),
				array(
					'display' => 'Saturday',
					'value'   => '6'
				)
			)
		),
		array(
			'type'  => 'text',
			'label' => 'Start Time',
			'name'  => 'time_start',
			'value' => ( !empty( $entry['time_start'] ) ? $entry['time_start'] : '' )
		),
		array(
			'type'  => 'text',
			'label' => 'End Time',
			'name'  => 'time_end',
			'value' => ( !empty( $entry['time_end'] ) ? $entry['time_end'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'time_condition_id',
			'value' => ( !empty( $entry['time_condition_id'] ) ? $entry['time_condition_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'time_group_id',
			'value' => ( isset( $entry['time_group_id'] ) ? $entry['time_group_id'] : $_GET['time_group_id'] )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $_GET['client_id'] ) ? $_GET['client_id'] : '' )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/time_group/entry-addedit-save.php" method="post">
	<?= form_display( $entry_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
