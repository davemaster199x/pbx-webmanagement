<?php namespace context;

	function save( $params ) {
	/*
	 * Save
	 * Save a context.
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

		if ( empty( $params['context_id'] )) {
		// Save context

			$pdo = dbh();

			$context_query = <<<SQL
  INSERT INTO context
     SET rpoint_id = :rpoint_id,
         context   = :context
SQL;
			$context_stmt = $pdo->prepare( $context_query );

			$context_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );
			$context_stmt->bindParam( ':context',   $params['context'],   \PDO::PARAM_STR );

			$context_stmt->execute();

			$context_id = $pdo->lastInsertId();

			if ( isset( $params['children'] ) && is_array( $params['children'] )) {

				$xref_query = <<<SQL
  INSERT INTO xref_context_context
     SET parent_id = :parent_id,
         child_id  = :child_id
SQL;
				$xref_stmt = $pdo->prepare( $xref_query );

				foreach ( $params['children'] as $child_id ) {

					$xref_stmt->bindParam( ':parent_id', $context_id, \PDO::PARAM_INT );
					$xref_stmt->bindParam( ':child_id',  $child_id,   \PDO::PARAM_INT );

					$xref_stmt->execute();
				}
			}

			return build_result( TRUE, 'context_saved', [ 'context_id' => $context_id ] );
		} else {
		// Update existing context

			$pdo = dbh();

			$pdo->beginTransaction();

			$context_query = <<<SQL
  UPDATE context
     SET rpoint_id  = :rpoint_id,
         context    = :context
   WHERE context_id = :context_id
SQL;
			$context_stmt = $pdo->prepare( $context_query );

			$context_stmt->bindParam( ':rpoint_id',  $params['rpoint_id'],  \PDO::PARAM_INT );
			$context_stmt->bindParam( ':context',    $params['context'],    \PDO::PARAM_STR );
			$context_stmt->bindParam( ':context_id', $params['context_id'], \PDO::PARAM_INT );

			$context_stmt->execute();

			$context_id = $params['context_id'];

			$delete_query = <<<SQL
  DELETE
    FROM xref_context_context
   WHERE parent_id = :parent_id
SQL;
			$delete_stmt = $pdo->prepare( $delete_query );

			$delete_stmt->bindParam( ':parent_id', $params['context_id'], \PDO::PARAM_INT );

			$delete_stmt->execute();

			if ( isset( $params['children'] ) && is_array( $params['children'] )) {

				$dupe_query = <<<SQL
  SELECT parent_id, child_id
    FROM xref_context_context
   WHERE parent_id = :child_id
     AND child_id  = :parent_id
SQL;
				$dupe_stmt = $pdo->prepare( $dupe_query );

				$xref_query = <<<SQL
  INSERT INTO xref_context_context
     SET parent_id = :parent_id,
         child_id  = :child_id
SQL;
				$xref_stmt = $pdo->prepare( $xref_query );

				foreach ( $params['children'] as $child_id ) {

					$dupe_stmt->bindParam( ':parent_id', $context_id, \PDO::PARAM_INT );
					$dupe_stmt->bindParam( ':child_id',  $child_id,   \PDO::PARAM_INT );

					$dupe_stmt->execute();

					if ( $dupe_stmt->rowCount() > 0 ) {

						$pdo->rollBack();

						return build_result( FALSE, 'context_not_saved', [ 'error' => 'Reciprocal relationship not allowed.' ] );
					}

					$xref_stmt->bindParam( ':parent_id', $context_id, \PDO::PARAM_INT );
					$xref_stmt->bindParam( ':child_id',  $child_id,   \PDO::PARAM_INT );

					$xref_stmt->execute();
				}
			}

			$pdo->commit();

			return build_result( TRUE, 'context_saved', [ 'context_id' => $params['context_id'] ] );
		}
	}

?>
