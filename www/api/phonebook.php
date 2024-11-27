<?php
    include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

    $jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

    header('Content-Type: text/xml');

// Fetch parameters
    $client_id    = isset( $_GET['client_id'] ) ? $_GET['client_id'] : '';
    $phonebook_id = isset( $_GET['phonebook_id'] ) ? $_GET['phonebook_id'] : '';
    $token        = isset( $_GET['token'] ) ? $_GET['token'] : '';

// Function to validate token
    function validate_token( $client_id, $token ) {
    // Fetch client http_username and http_password from database or predefined source
        $client_data = get_client_data( $client_id );
        
        if ( !$client_data ) {
            return false;
        }
        
        $expected_token = base64_encode( $client_data['http_username'] . ':' . $client_data['http_password'] );
        return $token === $expected_token;
    }

// Function to fetch client data
    function get_client_data( $client_id ) {
        
        global $jsonrpc_client, $config_client;

        $get_client = new jsonrpc\method( 'client.get' );
        $get_client->param( 'api_token', $config_client['jsonrpc']['api_token'] );
        $get_client->param( 'hash',      $_SESSION['user']['hash'] );
        $get_client->param( 'client_id',    $client_id );
        $get_client->id = $jsonrpc_client->generate_unique_id();

        $jsonrpc_client->method( $get_client );
        $jsonrpc_client->send();

        $result = jsonrpc\client::parse_result( $jsonrpc_client->result );

        return $result[ $get_client->id ]['data']['clients'][0] ?? '';
    }

// Validate token
    if ( !validate_token( $client_id, $token )) {
        echo 'Invalid token!';
        exit();
    }

// Function to fetch phonebook entries
    function get_phonebook_entries( $phonebook_id ) {
    // Replace this with actual database queries

        global $jsonrpc_client, $config_client;

        $get_entry = new jsonrpc\method( 'phonebook.get_entry' );
        $get_entry->param( 'api_token',     $config_client['jsonrpc']['api_token'] );
        $get_entry->param( 'hash',          $_SESSION['user']['hash'] );
        $get_entry->param( 'phonebook_id',  $phonebook_id );
        $get_entry->id = $jsonrpc_client->generate_unique_id();

        $jsonrpc_client->method( $get_entry );
        $jsonrpc_client->send();

        $result = jsonrpc\client::parse_result( $jsonrpc_client->result );
        
        return $result[ $get_entry->id ]['data']['entries'] ?? [];
    }

// Fetch phonebook entries
    $entries = get_phonebook_entries( $phonebook_id );

// Generate XML
    $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><AddressBook></AddressBook>' );
    $xml->addChild( 'version', '1' );

    foreach ( $entries as $entry ) {
        $contact = $xml->addChild( 'Contact' );
        $contact->addChild( 'FirstName', $entry['first_name'] );
        $contact->addChild( 'LastName', $entry['last_name'] );
        $contact->addChild( 'Frequent', '0');

        $phone = $contact->addChild( 'Phone' );
        $phone->addAttribute( 'type', $entry['type'] );
        $phone->addChild( 'phonenumber', preg_replace('/\D/', '', $entry['number'] ));
        $phone->addChild( 'accountindex', '0' );
    }

// Output XML
    echo $xml->asXML();
?>
