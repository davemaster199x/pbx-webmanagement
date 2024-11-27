<?php

	function file_save( $data, $type_id ) {
	/*
	 * Save
	 * Saves a file to the file manager using the provided
	 * base64-encoded data.
	 */

		global $_config;

		$pdo = \db_connect();

		$file_data = base64_decode( $data );
		$hash      = sha1( $file_data );

	// See if file already exists
		$file_query = <<<SQL
  SELECT file.file_id
    FROM file
   WHERE file.deleted = '0000-00-00 00:00:00'
     AND file.type_id = :type_id
     AND file.hash    = :hash
SQL;
		$file_stmt = $pdo->prepare( $file_query );

		$file_stmt->bindParam( ':type_id', $type_id, \PDO::PARAM_INT );
		$file_stmt->bindParam( ':hash',    $hash,    \PDO::PARAM_STR );

		$file_stmt->execute();

		if ( $file_stmt->rowCount() ) {
		// File exists, update timestamp, return existing ID

			$file_row = $file_stmt->fetch( PDO::FETCH_ASSOC );

			$update_query = <<<SQL
  UPDATE file
     SET updated = NOW()
   WHERE file_id = :file_id
SQL;
			$update_stmt = $pdo->prepare( $update_query );

			$update_stmt->bindParam( ':file_id', $file_row['file_id'], \PDO::PARAM_INT );

			$update_stmt->execute();

			return $file_row['file_id'];
		} else {
		// Save file data to filesystem, creating the destination directory if it doesn't already exist

			$file_path = $_config[ $_SERVER['SERVER_NAME'] ]['paths']['files'];

			if ( !file_exists( $file_path . '/' . substr( $hash, 0, 2 ))) {

				mkdir( $file_path . '/' . substr( $hash, 0, 2 ));
				chmod( $file_path . '/' . substr( $hash, 0, 2 ), 0777 );
			}

			file_put_contents( $file_path . '/' . substr( $hash, 0, 2 ) . '/' . $hash, $file_data );
			chmod( $file_path . '/' . substr( $hash, 0, 2 ) . '/' . $hash, 0777 );

		// Get mime type of saved data
			$mime = \mime_type( $file_path . '/' . substr( $hash, 0, 2 ) . '/' . $hash, $file_data );

			$file_query = <<<SQL
  INSERT INTO file
     SET type_id = :type_id,
         hash    = :hash,
         mime    = :mime
SQL;
			$file_stmt = $pdo->prepare( $file_query );

			$file_stmt->bindParam( ':type_id', $type_id, \PDO::PARAM_INT );
			$file_stmt->bindParam( ':hash',    $hash,    \PDO::PARAM_STR );
			$file_stmt->bindParam( ':mime',    $mime,    \PDO::PARAM_STR );

			$file_stmt->execute();

			return $pdo->lastInsertId();
		}
	}

?>
