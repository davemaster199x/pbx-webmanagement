<?php namespace file;

	function file_delete( $params ) {
	/*
	 * Delete
	 * Deletes a file in the file manager.
	 */
		global $_config;

		$pdo = \db_connect();

		if( empty( $params['file_id'] ) && empty( $params['filehash'] )) {
			return _build_response( ERROR, 'missing_file_id_or_filehash' );
		}

		$file_query = <<<SQL
  SELECT file_id, locked, hash
    FROM file
   WHERE file_id = :file_id
      OR hash    = :filehash
SQL;
		$file_result = $pdo->prepare( $file_query );
		$file_result->execute( array(
			':file_id'  => ( isset( $params['file_id'] )  ? $params['file_id']  : 0 ),
			':filehash' => ( isset( $params['filehash'] ) ? basename( $params['filehash'] ) : '' )
		));

		if ( $file_result->rowCount() ) {
			$file_row = $file_result->fetch( \PDO::FETCH_ASSOC );

			if ( !$file_row['locked'] ) {
				$file_query = <<<SQL
  UPDATE file
     SET deleted = :deleted
   WHERE file_id = :file_id
SQL;
				$file_result = $pdo->prepare( $file_query );
				$file_result->execute( array(
					':deleted' => date( 'Y-m-d H:i:s' ),
					':file_id' => $file_row['file_id']
				));

				$file_path = $_config[ $_SERVER['SERVER_NAME'] ]['paths']['files'];

				unlink( $file_path . '/' . substr( $file_row['hash'], 0, 2 ) . '/' . $file_row['hash'] );

				return _build_response( COMPLETE, 'file_deleted' );
			} else {
				return _build_response( ERROR, 'file_locked' );
			}
		} else {
			return _build_response( ERROR, 'file_not_found' );
		}
	}

?>
