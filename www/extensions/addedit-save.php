<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_extension = new jsonrpc\method( 'extension.save' );
	$save_extension->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$save_extension->param( 'hash',        $_SESSION['user']['hash'] );
	$save_extension->param( 'rpoint_id',   $_POST['rpoint_id'] );
	$save_extension->param( 'context_id',  $_POST['context_id'] );
	$save_extension->param( 'ext',         $_POST['ext'] );

	if ( !empty( $_POST['extension_id'] )) {

		$save_extension->param( 'extension_id', $_POST['extension_id'] );
		$save_extension->param( 'cmd',          $_POST['cmd'] );
		
		foreach ( $_POST['cmd'] as $index => $value ) {
			
			if ( $value == 'Dial' ) {

				$ringdelay_value = $_POST['ringdelay' . $index ];

			// Check if the value is empty, and set it to '00' if true
				foreach ( $ringdelay_value as $value ) {
					$formatted_value = ( $value == '' ) ? '00' : $value;

				// Check the length of the value and format accordingly
					if ( $formatted_value != '0' ) {
						if ( strlen( $formatted_value ) == 1 ) {
							$formatted_value = '0' . $formatted_value;
						}
					}

				// Add the formatted value to the result array
					$formatted_ringdelay_array[] = $formatted_value;
				}

				$save_extension->param( 'endpoint' . $index, isset( $_POST['endpoint' . $index ] ) ? $_POST['endpoint' . $index] : '' );
				$save_extension->param( 'ringdelay' . $index, isset( $formatted_ringdelay_array ) ? $formatted_ringdelay_array : '' );
				$save_extension->param( 'dial_input' . $index, isset( $_POST['dial_input' . $index ] ) ? $_POST['dial_input' . $index] : '' );
			} elseif ( $value == 'Goto' ) {
		
				$save_extension->param( 'ext_command' . $index, isset( $_POST['ext_command' . $index ] ) ? $_POST['ext_command' . $index ] : '' );
				$save_extension->param( 'prio' . $index,        isset( $_POST['prio' . $index ] )        ? $_POST['prio' . $index ]        : '' );
			} elseif ( $value == 'PlayBack' ) {

				$save_extension->param( 'sound_file' . $index, isset( $_POST['sound_file' . $index ] ) ? $_POST['sound_file' . $index ] : '' );
			} elseif ( $value == 'VoiceMail' ) {

				$save_extension->param( 'mailbox' . $index, isset( $_POST['mailbox' . $index ] ) ? $_POST['mailbox' . $index] : '' );
			} elseif ( $value == 'Wait' ) {
				
				$save_extension->param( 'seconds' . $index, isset( $_POST['seconds' . $index ] ) ? $_POST['seconds' . $index ] : '' );
			} elseif ( $value == 'Background' ) {
				
				$save_extension->param( 'sound_file_bg' . $index, isset( $_POST['sound_file_bg' . $index ] ) ? $_POST['sound_file_bg' . $index ] : '' );
			} elseif ( $value == 'Log' ) {
				
				$save_extension->param( 'log_level' . $index, isset( $_POST['log_level' . $index ] ) ? $_POST['log_level' . $index ] : '' );
				$save_extension->param( 'log' . $index, isset( $_POST['log' . $index ] ) ? $_POST['log' . $index ] : '' );
			} elseif ( $value == 'Set' ) {
				
				$save_extension->param( 'variable' . $index, isset( $_POST['variable' . $index ] ) ? $_POST['variable' . $index ] : '' );
			} elseif ( $value == 'Page' ) {
				
				$save_extension->param( 'endpoint_page' . $index, isset( $_POST['endpoint_page' . $index ] ) ? $_POST['endpoint_page' . $index ] : '' );
				$save_extension->param( 'page_input' . $index, isset( $_POST['page_input' . $index ] ) ? $_POST['page_input' . $index ] : '' );
			} elseif ( $value == 'WaitExten' ) {
				
				$save_extension->param( 'seconds_exten' . $index, isset( $_POST['seconds_exten' . $index ] ) ? $_POST['seconds_exten' . $index ] : '' );
			} elseif ( $value == 'GotoIfTime' ) {
				
				$save_extension->param( 'goto_time' . $index, isset( $_POST['goto_time' . $index ] ) ? $_POST['goto_time' . $index ] : '' );
				$save_extension->param( 'goto_day_week' . $index, isset( $_POST['goto_day_week' . $index ] ) ? $_POST['goto_day_week' . $index ] : '' );
				$save_extension->param( 'goto_day_month' . $index, isset( $_POST['goto_day_month' . $index ] ) ? $_POST['goto_day_month' . $index ] : '' );
				$save_extension->param( 'goto_month' . $index, isset( $_POST['goto_month' . $index ] ) ? $_POST['goto_month' . $index ] : '' );
				$save_extension->param( 'goto_true' . $index, isset( $_POST['goto_true' . $index ] ) ? $_POST['goto_true' . $index ] : '' );
				$save_extension->param( 'goto_false' . $index, isset( $_POST['goto_false' . $index ] ) ? $_POST['goto_false' . $index ] : '' );
			} elseif ( $value == 'VoiceMailMain' ) {
				
				$save_extension->param( 'voice_mailbox' . $index, isset( $_POST['voice_mailbox' . $index ] ) ? $_POST['voice_mailbox' . $index ] : '' );
				$save_extension->param( 'voice_mailbox_text' . $index, isset( $_POST['voice_mailbox_text' . $index ] ) ? $_POST['voice_mailbox_text' . $index ] : '' );
			} else {
			// Unrecognized dialplan

				$concatenatedValue = $value . $index;
				$save_extension->param( $concatenatedValue, isset( $_POST[ $concatenatedValue ] ) ? $_POST[ $concatenatedValue ] : '');
			}
		}
	}

	$save_extension->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_extension );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_extension->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_extension->id ]['message'];
	}

	header( 'Location: /extensions' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
