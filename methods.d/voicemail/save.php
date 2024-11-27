<?php namespace voicemail;

	function save( $params ) {
	/*
	 * Save
	 * Save a voicemail.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log', 'pbx_api' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

		$pdo = dbh();

		$pdo->beginTransaction();

		if ( empty( $params['voicemail_id'] )) {
		// Save voicemail

			$voicemail_query = <<<SQL
  INSERT INTO voicemail
     SET rpoint_id = :rpoint_id,
         mailbox     = :mailbox,
         password    = :password,
         name        = :name,
         email       = :email,
         options     = :options
SQL;
			$voicemail_stmt = $pdo->prepare( $voicemail_query );

			$voicemail_stmt->bindParam( ':rpoint_id',   $params['rpoint_id'],   \PDO::PARAM_INT );
			$voicemail_stmt->bindParam( ':mailbox',     $params['mailbox'],     \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':name',        $params['name'],        \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':password',    $params['password'],    \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':email',       $params['email'],       \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':options',     $params['options'],     \PDO::PARAM_STR );

			if ( $voicemail_stmt->execute() ) {
			// Mailbox saved, make API call

				$voicemail_id = $pdo->lastInsertId();

				$rpoint_query = <<<SQL
  SELECT *
    FROM client_rpoint
   WHERE rpoint_id = :rpoint_id
SQL;
				$rpoint_stmt = $pdo->prepare( $rpoint_query );

				$rpoint_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

				$rpoint_stmt->execute();

				$rpoint_row = $rpoint_stmt->fetch( \PDO::PARAM_INT );

				$url_parts = parse_url( $rpoint_row['api_endpoint'] );

				if ( isset( $url_parts['port'] )) {

					$port = $url_parts['port'];
				} else {

					$port = $config_server['api']['config']['port'];
				}

				$voicemail_query = <<<SQL
  SELECT mailbox, password, name, email, options
    FROM voicemail
   WHERE rpoint_id = :rpoint_id
SQL;
				$voicemail_stmt = $pdo->prepare( $voicemail_query );

				$voicemail_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

				$voicemail_stmt->execute();

				$mailboxes = [];

				while( $voicemail_row = $voicemail_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$mailboxes[] = [
						'mailbox'  => $voicemail_row['mailbox'],
						'password' => $voicemail_row['password'],
						'name'     => $voicemail_row['name'],
						'email'    => $voicemail_row['email'],
						'options'  => $voicemail_row['options']
					];
				}

				$result = \pbx_api(
					'Voicemail',
					[
						'protocol' => $url_parts['scheme'],
						'server'   => $url_parts['host'],
						'port'     => $port,
						'version'  => $config_server['api']['config']['version']
					],
					$rpoint_row['api_user'],
					$rpoint_row['api_password'],
					$mailboxes
				);

				if ( !$result['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'voicemail_not_saved', [ 'error' => 'API error: ' . print_r( $result['data'], TRUE ) ] );
				} else {
				// API call succeded, commit
					$pdo->commit();

					return build_result( TRUE, 'voicemail_saved', [ 'voicemail_id' => $voicemail_id ] );
				}
			} else {

				return build_result( FALSE, 'voicemail_not_saved', [ 'error' => $voicemail_stmt->errorInfo() ] );
			}
		} else {
		// Update existing voicemail
			$voicemail_query = <<<SQL
  UPDATE voicemail
     SET rpoint_id    = :rpoint_id,
         mailbox      = :mailbox,
         name         = :name,
         password     = :password,
         email        = :email,
         options      = :options
   WHERE voicemail_id = :voicemail_id
SQL;
			$voicemail_stmt = $pdo->prepare( $voicemail_query );

			$voicemail_stmt->bindParam( ':rpoint_id',    $params['rpoint_id'],    \PDO::PARAM_INT );
			$voicemail_stmt->bindParam( ':mailbox',      $params['mailbox'],      \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':name',         $params['name'],         \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':password',     $params['password'],     \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':email',        $params['email'],        \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':options',      $params['options'],      \PDO::PARAM_STR );
			$voicemail_stmt->bindParam( ':voicemail_id', $params['voicemail_id'], \PDO::PARAM_INT );

			if ( $voicemail_stmt->execute() ) {
			// Mailbox saved, make API call

				$rpoint_query = <<<SQL
  SELECT *
    FROM client_rpoint
   WHERE rpoint_id = :rpoint_id
SQL;
				$rpoint_stmt = $pdo->prepare( $rpoint_query );

				$rpoint_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

				$rpoint_stmt->execute();

				$rpoint_row = $rpoint_stmt->fetch( \PDO::PARAM_INT );

				$url_parts = parse_url( $rpoint_row['api_endpoint'] );

				if ( isset( $url_parts['port'] )) {

					$port = $url_parts['port'];
				} else {

					$port = $config_server['api']['config']['port'];
				}

				$voicemail_query = <<<SQL
  SELECT mailbox, password, name, email, options
    FROM voicemail
   WHERE rpoint_id = :rpoint_id
SQL;
				$voicemail_stmt = $pdo->prepare( $voicemail_query );

				$voicemail_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

				$voicemail_stmt->execute();

				$mailboxes = [];

				while( $voicemail_row = $voicemail_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$mailboxes[] = [
						'mailbox'  => $voicemail_row['mailbox'],
						'password' => $voicemail_row['password'],
						'name'     => $voicemail_row['name'],
						'email'    => $voicemail_row['email'],
						'options'  => $voicemail_row['options']
					];
				}

				$result = \pbx_api(
					'Voicemail',
					[
						'protocol' => $url_parts['scheme'],
						'server'   => $url_parts['host'],
						'port'     => $port,
						'version'  => $config_server['api']['config']['version']
					],
					$rpoint_row['api_user'],
					$rpoint_row['api_password'],
					$mailboxes
				);

				if ( !$result['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'voicemail_not_saved', [ 'error' => 'API error: ' . print_r( $result['data'], TRUE ) ] );
				} else {
				// API call succeded, commit
					$pdo->commit();

					return build_result( TRUE, 'voicemail_saved', [ 'voicemail_id' => $params['voicemail_id'] ] );
				}
			} else {

				return build_result( FALSE, 'voicemail_not_saved', [ 'error' => $voicemail_stmt->errorInfo() ] );
			}
		}
	}

?>
