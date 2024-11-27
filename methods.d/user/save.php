<?php namespace user;

	function save( $params ) {
	/*
	 * Save
	 * Save a user.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log' ] );

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

	// Make sure email is unique
		$unique_query = <<<SQL
  SELECT user.user_id
    FROM user
   WHERE user.email    = :email
     AND user.email   != ''
     AND user.user_id != :user_id
SQL;
		$unique_stmt = $pdo->prepare( $unique_query );

		$unique_stmt->bindParam( ':email', $params['email'], \PDO::PARAM_STR );

		$unique_stmt->bindValue( ':user_id', isset( $params['user_id'] ) ? $params['user_id'] : 0, \PDO::PARAM_INT );

		$unique_stmt->execute();

		if ( $unique_stmt->rowCount() ) {

			return build_result( FALSE, 'email_not_unique' );
		}

	// Parameters are valid, continue on
		$cdel_query = <<<SQL
  DELETE
    FROM xref_client_user
   WHERE user_id = :user_id
SQL;
		$cdel_stmt = $pdo->prepare( $cdel_query );

		$cadd_query = <<<SQL
  INSERT INTO xref_client_user
     SET client_id = :client_id,
         user_id   = :user_id
SQL;
		$cadd_stmt = $pdo->prepare( $cadd_query );

		if ( empty( $params['user_id'] )) {
		// Save new user
			$user_query = <<<SQL
  INSERT INTO user
     SET active     = :active,
         is_global  = :is_global,
         email      = :email,
         password   = :password,
         first_name = :first_name,
         last_name  = :last_name
SQL;
			$user_stmt = $pdo->prepare( $user_query );

			$user_stmt->bindParam( ':active',     $params['active'],     \PDO::PARAM_INT );
			$user_stmt->bindParam( ':is_global',  $params['is_global'],  \PDO::PARAM_INT );
			$user_stmt->bindParam( ':email',      $params['email'],      \PDO::PARAM_STR );
			$user_stmt->bindParam( ':first_name', $params['first_name'], \PDO::PARAM_STR );
			$user_stmt->bindParam( ':last_name',  $params['last_name'],  \PDO::PARAM_STR );

			$user_stmt->bindValue( ':password', password_hash( $params['password'], PASSWORD_DEFAULT ), \PDO::PARAM_STR );

			if ( $user_stmt->execute() ) {

				$user_id = $pdo->lastInsertId();

				if ( !empty( $params['clients'] )) {

					foreach ( $params['clients'] as $client_id => $value ) {

						$cadd_stmt->bindParam( ':client_id', $client_id, \PDO::PARAM_INT );
						$cadd_stmt->bindParam( ':user_id',   $user_id,   \PDO::PARAM_INT );

						$cadd_stmt->execute();
					}
				}

				return build_result( TRUE, 'user_saved', [ 'user_id' => $pdo->lastInsertId() ] );
			} else {

				return build_result( FALSE, 'user_not_saved', [ 'error' => $user_stmt->errorInfo() ] );
			}
		} else {
		// Update existing user

			$user_query = <<<SQL
  UPDATE user
     SET active     = :active,
         is_global  = :is_global,
         email      = :email,
         first_name = :first_name,
         last_name  = :last_name
   WHERE user_id    = :user_id
SQL;
			$user_stmt = $pdo->prepare( $user_query );

			$user_stmt->bindParam( ':active',     $params['active'],     \PDO::PARAM_INT );
			$user_stmt->bindParam( ':is_global',  $params['is_global'],  \PDO::PARAM_INT );
			$user_stmt->bindParam( ':email',      $params['email'],      \PDO::PARAM_STR );
			$user_stmt->bindParam( ':first_name', $params['first_name'], \PDO::PARAM_STR );
			$user_stmt->bindParam( ':last_name',  $params['last_name'],  \PDO::PARAM_STR );
			$user_stmt->bindParam( ':user_id',    $params['user_id'],    \PDO::PARAM_INT );

			if ( !$user_stmt->execute() ) {

				return build_result( FALSE, 'user_not_saved', [ 'error' => $user_stmt->errorInfo() ] );
			}

			if ( !empty( $params['password'] )) {

				$password_query = <<<SQL
  UPDATE user
     SET password = :password
   WHERE user_id  = :user_id
SQL;
				$password_stmt = $pdo->prepare( $password_query );

				$password_stmt->bindValue( ':password', password_hash( $params['password'], PASSWORD_DEFAULT ), \PDO::PARAM_STR );

				$password_stmt->bindParam( ':user_id', $params['user_id'], \PDO::PARAM_INT );

				$password_stmt->execute();
			}

			$cdel_stmt->bindParam( ':user_id', $params['user_id'], \PDO::PARAM_INT );

			$cdel_stmt->execute();

			if ( !empty( $params['clients'] )) {

				foreach ( $params['clients'] as $client_id => $value ) {

					$cadd_stmt->bindParam( ':client_id', $client_id,         \PDO::PARAM_INT );
					$cadd_stmt->bindParam( ':user_id',   $params['user_id'], \PDO::PARAM_INT );

					$cadd_stmt->execute();
				}
			}

			return build_result( TRUE, 'user_saved', [ 'user_id' => $params['user_id'] ] );
		}
	}

?>
