<?php

	function loggedin() {
	/*
	 * Checks for the existence of $_SESSION['user_row']['user_id'] to see if the user is logged in.
	 *
	 * @return bool - TRUE got logged in, FALSE for not logged in.
	 */

		if ( !empty( $_SESSION['user']['user_id'] )) {

			return TRUE;
		} else {

			return FALSE;
		}
	}

?>
