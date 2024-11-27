<?php namespace file;

	function file_save( $params ) {
	/*
	 * Save
	 * Saves a file to the file manager.
	 */
		global $_config;

		$pdo = \db_connect();

	// Verify hash
		$user_id = _verify_hash( $params['hash'] );
		if( !$user_id ) {
			return _build_response( ERROR, "invalid_hash" );
		}

		if( !isset( $params['file_id'] ) && empty( $params['name'] )) {
			return _build_response( ERROR, "missing_file_name" );
		}

		if( empty( $params['file_id'] ) && !isset( $params['folder_id'] )) {
			return _build_response( ERROR, "missing_folder_id" );
		}

		if( !empty( $params['file_id'] ) && !isset( $params['file_data'] )) {
			return _build_response( ERROR, "missing_file_data" );
		}

		if( empty( $params['file_id'] )) {
		// Creating new file entry, verify that it is unique in this folder
			$file_query = <<<SQL
  SELECT *
    FROM file
   WHERE name      = :name
     AND folder_id = :folder_id
SQL;
			$file_result = $pdo->prepare( $file_query );
			$file_result->execute( array(
				":name"      => $params['name'],
				":folder_id" => $params['folder_id']
			));

			if( $file_result->rowCount() ) {
				return _build_response( ERROR, "file_already_exists" );
			}

			$file_query = <<<SQL
  INSERT INTO file
     SET name      = :name,
         folder_id = :folder_id
SQL;
			$file_result = $pdo->prepare( $file_query );
			$file_result->execute( array(
				":name"      => $params['name'],
				":folder_id" => $params['folder_id']
			));

			return _build_response( COMPLETE, "file_entry_created", $pdo->lastInsertId() );
		} else {
		// File entry exists, save file data to disk
			$file_data = base64_decode( $params['file_data'] );
			$hash      = sha1( $file_data );

		// Save file data to filesystem, creating the destination directory if it doesn't already exist
			$file_path = $_config[ $_SERVER['SERVER_NAME'] ]['paths']['files'];

			if( !file_exists( $file_path . "/" . substr( $hash, 0, 2 ))) {
				mkdir( $file_path . "/" . substr( $hash, 0, 2 ));
				chmod( $file_path . "/" . substr( $hash, 0, 2 ), 0777 );
			}

			file_put_contents( $file_path . "/" . substr( $hash, 0, 2 ) . "/" . $hash, $file_data );
			chmod( $file_path . "/" . substr( $hash, 0, 2 ) . "/" . $hash, 0777 );

		// Get mime type of saved data
			$type = \mime_type( $file_path . "/" . substr( $hash, 0, 2 ) . "/" . $hash, $file_data );

			$file_query = <<<SQL
  UPDATE file
     SET hash    = :hash,
         type    = :type
   WHERE file_id = :file_id
SQL;
			$file_result = $pdo->prepare( $file_query );
			$file_result->execute( array(
				":hash"    => $hash,
				":type"    => $type,
				":file_id" => $params['file_id']
			));

			return _build_response( COMPLETE, "file_data_saved" );
		}
	}

?>
