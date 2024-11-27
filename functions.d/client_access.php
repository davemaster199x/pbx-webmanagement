<?php

	function client_access( $user_id ) {
	/**
	 * Get a list of IDs that the specified user has access to.
	 *
	 * @param user_id int - The user for which to check access.
	 *
	 * @return array - The list of IDs for which the user has access.
	 */

	// See which clients the user has access to
		$user_query = <<<SQL
  SELECT is_global
    FROM user
   WHERE user_id = :user_id
SQL;
		$user_stmt = dbh()->prepare( $user_query );

		$user_stmt->bindParam( ':user_id', $user_id, \PDO::PARAM_INT );

		$user_stmt->execute();

		$user_row = $user_stmt->fetch( \PDO::FETCH_ASSOC );

		if ( $user_row['is_global'] ) {
		// User is a global admin, get all client IDs

			$id_query = <<<SQL
  SELECT client_id
    FROM client
SQL;
			$id_stmt = dbh()->prepare( $id_query );

			$id_stmt->execute();

			$id_rows = $id_stmt->fetchAll( \PDO::FETCH_COLUMN );
		} else {
		// User is unprivileged, get from xref

			$id_query = <<<SQL
  SELECT client_id
    FROM xref_client_user
   WHERE user_id = :user_id
SQL;
			$id_stmt = dbh()->prepare( $id_query );

			$id_stmt->bindParam( ':user_id', $user_id, \PDO::PARAM_INT );

			$id_stmt->execute();

			$id_rows = $id_stmt->fetchAll( \PDO::FETCH_COLUMN );
		}

		return $id_rows;
	}

?>
