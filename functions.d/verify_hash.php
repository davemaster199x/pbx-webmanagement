<?php

	function verify_hash( $hash ) {
	/*
	 * Verify Hash
	 * Returns user_id of user with specified login hash
	 */

		\function_init( [ 'dbh' ] );

		$user_query = <<<SQL
  SELECT user_id
    FROM user
   WHERE hash  = :hash
     AND hash != ''
SQL;
		$user_stmt = dbh()->prepare( $user_query );

		$user_stmt->bindParam( ':hash', $hash, \PDO::PARAM_STR );

		$user_stmt->execute();

		$user_row = $user_stmt->fetch( PDO::FETCH_ASSOC );

		return $user_row['user_id'];
	}

?>
