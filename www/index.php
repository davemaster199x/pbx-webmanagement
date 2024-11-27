<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( isset( $_GET['from_terminal'] ) && $_GET['from_terminal'] == 'true' ) {
		$_SESSION['from_terminal'] = TRUE;
		$_SESSION['return_host']   = $_GET['return_host'];

		header( 'Location: /' );
	}

	if ( loggedin() ) {
		header( 'Location: /dashboard' );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header-login.php" ); ?>
<section id="login-form">
	<div>
		<?php if ( isset( $_GET['error'] ) && $_GET['error'] == 'bad_authentication' ) : ?>
			<span class="error">Invalid username or password...</span>
		<?php endif; ?>
		<form action="/login.php" method="post">
			<div class="input">
				<span class="label"></span>
				<span class="input">
					<input type="text" name="username" placeholder="Username">
				</span>
			</div><!-- /.input -->
			<div class="input">
				<span class="label"></span>
				<span class="input">
					<input type="password" name="password" placeholder="Password">
				</span>
			</div><!-- /.input -->
			<div class="input">
				<span class="label"></span>
				<span class="input">
					<input type="submit" name="submit" value="Login">
				</span>
			</div><!-- /.input -->
			<?php if ( isset( $_GET['return'] )) : ?>
				<input type="hidden" name="return" value="<?= $_GET['return']; ?>">
			<?php endif; ?>
			<div class="poweredby">
				Powered by Hyperion Works
			</div>
		</form>
	</div>
</section><!-- /#login-form -->
<script type="text/javascript">

	$( document ).ready( function() {
		$( '[name=username]' ).focus();

		<?php if ( isset( $_SESSION['return_host'] )) : ?>
			$( '[name=from_terminal]' ).on( 'click', function( ev ) {
				window.location.href = 'http://<?= $_SESSION['return_host']; ?>';
			} );
		<?php endif; ?>
	} );

</script>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer-login.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
