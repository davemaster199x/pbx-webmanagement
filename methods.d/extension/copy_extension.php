<?php namespace extension;

	function copy_extension( $params ) {
	/*
	 * Get
	 * Duplicate extension.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'client_access', 'dbh', 'verify_hash', 'audit_log' ] );

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

    // Get extension
        $query = <<<SQL
  SELECT extension.*
	FROM extension  
   WHERE extension_id = :extension_id
SQL;
        $stmt = $pdo->prepare( $query );

        $stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

        $stmt->execute();

        $extension = $stmt->fetchAll( \PDO::FETCH_ASSOC );

    // Insert the duplicate extension
        $query = <<<SQL
  INSERT INTO extension
     SET rpoint_id  = :rpoint_id,
	     context_id = :context_id,
         ext        = :ext  
SQL;
        $stmt = $pdo->prepare( $query );

        $stmt->bindParam( ':rpoint_id',  $extension[0]['rpoint_id'],    \PDO::PARAM_INT );
        $stmt->bindParam( ':context_id', $extension[0]['context_id'],   \PDO::PARAM_INT );
        $stmt->bindParam( ':ext',        $params['new_extension_name'], \PDO::PARAM_STR );

        $stmt->execute();

        $extension_id = $pdo->lastInsertId();

    // Get ext dialplan 
        $query = <<<SQL
  SELECT *
	FROM extension_dialplan   
   WHERE extension_id = :extension_id
SQL;
        $stmt = $pdo->prepare( $query );

        $stmt->bindParam( ':extension_id', $params['extension_id'], \PDO::PARAM_INT );

        $stmt->execute();

        $extension_dialplan = $stmt->fetchAll( \PDO::FETCH_ASSOC );  

        foreach ( $extension_dialplan as $data ) {
        // Insert the duplicate extension dialplan
            $query = <<<SQL
  INSERT INTO extension_dialplan
     SET extension_id  = :extension_id,
	     prio          = :prio,
         cmd           = :cmd  
SQL;
            $stmt = $pdo->prepare( $query );

            $stmt->bindParam( ':extension_id',  $extension_id,   \PDO::PARAM_INT );
            $stmt->bindParam( ':prio',          $data['prio'],   \PDO::PARAM_INT );
            $stmt->bindParam( ':cmd',           $data['cmd'],    \PDO::PARAM_STR );

            $stmt->execute();

            $dialplan_id = $pdo->lastInsertId();

        // Get ext dialplan param
            $query = <<<SQL
  SELECT *
	FROM extension_dialplan_param   
   WHERE dialplan_id = :dialplan_id
SQL;
            $stmt = $pdo->prepare( $query );

            $stmt->bindParam( ':dialplan_id', $data['dialplan_id'], \PDO::PARAM_INT );

            $stmt->execute();

            $extension_dialplan_param = $stmt->fetchAll( \PDO::FETCH_ASSOC );  

            foreach ( $extension_dialplan_param as $param ) {
            // Insert the duplicate extension dialplan param
                $query = <<<SQL
  INSERT INTO extension_dialplan_param
     SET dialplan_id  = :dialplan_id,
	     `order`      = :order,
         param        = :param  
SQL;
                $stmt = $pdo->prepare( $query );

                $stmt->bindParam( ':dialplan_id',  $dialplan_id,     \PDO::PARAM_INT );
                $stmt->bindParam( ':order',        $param['order'],  \PDO::PARAM_INT );
                $stmt->bindParam( ':param',        $param['param'],  \PDO::PARAM_STR );

                $stmt->execute();
            }
        }

		return build_result( TRUE, 'new_extension', [ 'new_extension' => $extension ] );
	}

?>
