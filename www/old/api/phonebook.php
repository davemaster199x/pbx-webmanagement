<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['client_id'] ) && !isset( $_GET['phonebook_id'] ) && !isset( $_GET['token'] )) {

		exit();
	}

	if ( $_GET['token'] != 'QmBgQRgP8Ni7BVHe' ) {

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_entries = new jsonrpc\method( 'phonebook.get_phonebook' );
	$get_entries->param( 'api_token',    $jsonrpc['api_token'] );
	$get_entries->param( 'client_id',    $_GET['client_id'] );
	$get_entries->param( 'phonebook_id', $_GET['phonebook_id'] );
	$get_entries->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entries );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	header( 'Content-type: text/xml' );

	if ( $result[ $get_entries->id ]['status'] ) {
		$entries = $result[ $get_entries->id ]['data']['entry'];

		echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<AddressBook>
	<version>1</version>

XML;

		foreach ( $entries as $entry ) {

			foreach ( $entry as $key => $value ) {

				$entry[ $key ] = str_replace(
					[
						'&'
					], [
						'&amp;'
					], $entry[ $key ]
				);
			}

			$number = preg_replace( '/[^0-9]/', '', $entry['number'] );

			echo <<<XML
	<Contact>
		<FirstName>{$entry['first_name']}</FirstName>
		<LastName>{$entry['last_name']}</LastName>
		<Frequent>0</Frequent>
		<Phone type="{$entry['type']}">
			<phonenumber>$number</phonenumber>
			<accountindex>0</accountindex>
		</Phone>
	</Contact>

XML;
		}

		echo <<<XML
</AddressBook>

XML;
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
