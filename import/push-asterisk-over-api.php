<?php

// Include config
	include_once( __DIR__ . '/../config.php' );

// Include API function
	include_once( __DIR__ . '/../functions.d/pbx_api.php' );
	include_once( __DIR__ . '/../functions.d/dbh.php' );

// Set client
	$client_id = 1;

// Setup environment
	class env {
	/**
	 * Store values that can be easily accessed by user methods.
	 */

		public static $paths;
		public static $server;

	}

	\env::$paths['methods'] = __DIR__ . '/../methods.d/';

// Get all client endpoint details
	$endpoint_query = <<<SQL
  SELECT endpoint.name, endpoint.label, endpoint.name, endpoint.password, endpoint.context, endpoint.transport, endpoint.callerid, endpoint.mailboxes,
         device_type.name AS device_type,
         client_rpoint.rpoint_id,
         client.http_username, client.http_password
    FROM endpoint
           INNER JOIN device_type
                   ON endpoint.device_type_id = device_type.type_id
           INNER JOIN client_rpoint
                   ON endpoint.rpoint_id = client_rpoint.rpoint_id
           INNER JOIN client
                   ON client_rpoint.client_id = client.client_id
   WHERE client.client_id = :client_id
SQL;
	$endpoint_stmt = dbh()->prepare( $endpoint_query );

	$endpoint_stmt->bindParam( ':client_id', $client_id, PDO::PARAM_INT );

	$endpoint_stmt->execute();

	$sip_payload = [];

	while ( $endpoint_row = $endpoint_stmt->fetch( PDO::FETCH_ASSOC )) {

		if ( !isset( $sip_payload[ $endpoint_row['rpoint_id'] ] )) {

			$sip_payload[ $endpoint_row['rpoint_id'] ] = [];
		}

		$sip_payload[ $endpoint_row['rpoint_id'] ][] = [
			'name'      => $endpoint_row['name'],
			'password'  => $endpoint_row['password'],
			'context'   => $endpoint_row['context'],
			'transport' => $endpoint_row['transport'],
			'callerid'  => $endpoint_row['callerid'],
			'mailboxes' => $endpoint_row['mailboxes']
		];

	}

// Get all dialplan details
	$context_query = <<<SQL
  SELECT context.context_id, context.rpoint_id, context.context
    FROM context
           INNER JOIN client_rpoint
                   ON context.rpoint_id = client_rpoint.rpoint_id
   WHERE client_rpoint.client_id = :client_id
SQL;
	$context_stmt = dbh()->prepare( $context_query );

	$include_query = <<<SQL
  SELECT context.context_id, context.context
    FROM context
           INNER JOIN xref_context_context
                   ON context.context_id = xref_context_context.child_id
   WHERE xref_context_context.parent_id = :context_id
SQL;
	$include_stmt = dbh()->prepare( $include_query );

	$extension_query = <<<SQL
  SELECT extension.extension_id, extension.rpoint_id, extension.ext
    FROM extension
           INNER JOIN client_rpoint
                   ON extension.rpoint_id = client_rpoint.rpoint_id
   WHERE client_rpoint.client_id = :client_id
     AND extension.context_id    = :context_id
SQL;
	$extension_stmt = dbh()->prepare( $extension_query );

	$dialplan_query = <<<SQL
  SELECT extension_dialplan.dialplan_id, extension_dialplan.cmd
    FROM extension_dialplan
   WHERE extension_dialplan.extension_id = :extension_id
   ORDER BY extension_dialplan.prio
SQL;
	$dialplan_stmt = dbh()->prepare( $dialplan_query );

	$param_query = <<<SQL
  SELECT extension_dialplan_param.param
    FROM extension_dialplan_param
   WHERE extension_dialplan_param.dialplan_id = :dialplan_id
SQL;
	$param_stmt = dbh()->prepare( $param_query );

// Get all contexts for this client
	$context_stmt->bindParam( ':client_id', $client_id, PDO::PARAM_INT );

	$context_stmt->execute();

	$dialplan_payload = [];

	while ( $context_row = $context_stmt->fetch( PDO::FETCH_ASSOC )) {

		$include_stmt->bindParam( ':context_id', $context_row['context_id'], PDO::PARAM_INT );

		$include_stmt->execute();

		while ( $include_row = $include_stmt->fetch( PDO::FETCH_ASSOC )) {

			if ( !isset( $dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'] )) {

				$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'] = [];
			}

			$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['include'][] = $include_row['context'];
		}

		$extension_stmt->bindParam( ':client_id',  $client_id,                 PDO::PARAM_INT );
		$extension_stmt->bindParam( ':context_id', $context_row['context_id'], PDO::PARAM_INT );

		$extension_stmt->execute();

		while ( $extension_row = $extension_stmt->fetch( PDO::FETCH_ASSOC )) {

			$dialplan_stmt->bindParam( ':extension_id', $extension_row['extension_id'], PDO::PARAM_INT );

			$dialplan_stmt->execute();

			if ( !isset( $dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'] )) {

				$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'] = [];
			}

			$exten = [];

			while ( $dialplan_row = $dialplan_stmt->fetch( PDO::FETCH_ASSOC )) {

				$param_stmt->bindParam( ':dialplan_id', $dialplan_row['dialplan_id'], PDO::PARAM_INT );

				$param_stmt->execute();

				$params = implode( ',', $param_stmt->fetchAll( PDO::FETCH_COLUMN ));

				$exten[] = [
					'cmd'   => $dialplan_row['cmd'],
					'param' => $params
				];
			}

			$dialplan_payload[ $context_row['rpoint_id'] ][ $context_row['context'] ]['exten'][ $extension_row['ext'] ] = $exten;
		}
	}

// Get rpoint
	$rpoint_query = <<<SQL
  SELECT *
    FROM client_rpoint
   WHERE rpoint_id = :rpoint_id
SQL;
	$rpoint_stmt = dbh()->prepare( $rpoint_query );

	foreach ( $sip_payload as $rpoint_id => $rpoint_payload ) {

		$rpoint_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );

		$rpoint_stmt->execute();

		$rpoint_row = $rpoint_stmt->fetch( PDO::PARAM_INT );

		echo "Submitting endpoints to {$rpoint_row['api_endpoint']} using SIPEndpoints method...\n";

		$url_parts = parse_url( $rpoint_row['api_endpoint'] );

		if ( isset( $url_parts['port'] )) {

			$port = $url_parts['port'];
		} else {

			$port = $config_server['api']['config']['port'];
		}

		$result = \pbx_api(
			'SIPEndpoints',
			[
				'protocol' => $url_parts['scheme'],
				'server'   => $url_parts['host'],
				'port'     => $port,
				'version'  => $config_server['api']['config']['version']
			],
			$rpoint_row['api_user'],
			$rpoint_row['api_password'],
			$rpoint_payload
		);

		echo "  Result: " . json_encode( $result ) . "\n";
	}

	foreach ( $dialplan_payload as $rpoint_id => $rpoint_payload ) {

		$rpoint_stmt->bindParam( ':rpoint_id', $rpoint_id, PDO::PARAM_INT );

		$rpoint_stmt->execute();

		$rpoint_row = $rpoint_stmt->fetch( PDO::PARAM_INT );

		echo "Submitting extensions to {$rpoint_row['api_endpoint']} using Extensions method...\n";

		$url_parts = parse_url( $rpoint_row['api_endpoint'] );

		if ( isset( $url_parts['port'] )) {

			$port = $url_parts['port'];
		} else {

			$port = $config_server['api']['config']['port'];
		}

		$result = \pbx_api(
			'Extensions',
			[
				'protocol' => $url_parts['scheme'],
				'server'   => $url_parts['host'],
				'port'     => $port,
				'version'  => $config_server['api']['config']['version']
			],
			$rpoint_row['api_user'],
			$rpoint_row['api_password'],
			$rpoint_payload
		);

		echo "  Result: " . json_encode( $result ) . "\n";
	}

?>
