<?php
/**
 * Core email/password sign-in on the account page.
 *
 * @package ForWP\Account\Auth
 */

namespace ForWP\Account\Auth;

use ForWP\Account\Account\AccountMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress login / register / lost-password without wp-login.php chrome.
 */
class PasswordAuth {

	public const PROVIDER_ID = 'email';

	private const NONCE_ACTION = 'forwp_account_password';
	private const NONCE_FIELD  = 'forwp_account_password_nonce';

	/**
	 * Bootstrap POST handling.
	 */
	public static function init(): void {
		add_action( 'template_redirect', array( self::class, 'maybe_handle' ), 1 );
	}

	/**
	 * Whether the password form is enabled.
	 */
	public static function is_enabled(): bool {
		return ProviderSettings::is_enabled( self::PROVIDER_ID );
	}

	/**
	 * Whether registration fields may be shown (WordPress membership).
	 */
	public static function can_register(): bool {
		return self::is_enabled() && (bool) get_option( 'users_can_register' );
	}

	/**
	 * Active form panel: login|register|lostpassword.
	 */
	public static function current_mode(): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public form switch.
		$mode = isset( $_GET['form'] ) ? sanitize_key( wp_unslash( (string) $_GET['form'] ) ) : 'login';

		if ( 'register' === $mode && ! self::can_register() ) {
			return 'login';
		}

		if ( ! in_array( $mode, array( 'login', 'register', 'lostpassword' ), true ) ) {
			return 'login';
		}

		return $mode;
	}

	/**
	 * Handle login / register / lost-password POST.
	 */
	public static function maybe_handle(): void {
		if ( is_user_logged_in() || is_admin() || ! self::is_enabled() ) {
			return;
		}

		if ( ! isset( $_POST['forwp_account_form_action'] ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_FIELD ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION ) ) {
			self::fail( __( 'The form has expired. Please try again.', '4wp-account' ) );
		}

		$action = sanitize_key( wp_unslash( (string) $_POST['forwp_account_form_action'] ) );

		if ( 'login' === $action ) {
			self::handle_login();
		} elseif ( 'register' === $action ) {
			self::handle_register();
		} elseif ( 'lostpassword' === $action ) {
			self::handle_lost_password();
		}
	}

	/**
	 * Markup for the guest password form.
	 */
	public static function render(): string {
		if ( ! self::is_enabled() ) {
			return '';
		}

		$mode     = self::current_mode();
		$base_url = AccountMenu::get_account_page_url();

		ob_start();
		?>
		<div class="forwp-account-password">
			<?php
			if ( 'register' === $mode ) {
				self::render_register_form( $base_url );
			} elseif ( 'lostpassword' === $mode ) {
				self::render_lost_password_form( $base_url );
			} else {
				self::render_login_form( $base_url );
			}
			?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param string $base_url Account page URL.
	 */
	private static function render_login_form( string $base_url ): void {
		$login_value = '';
		if ( isset( $_POST['forwp_account_log'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Repopulate after failed attempt.
			$login_value = sanitize_text_field( wp_unslash( (string) $_POST['forwp_account_log'] ) );
		}
		?>
		<form class="forwp-account-password-form" method="post" action="<?php echo esc_url( $base_url ); ?>">
			<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
			<input type="hidden" name="forwp_account_form_action" value="login">

			<div class="forwp-account-password-fields">
			<p class="forwp-account-password-field">
				<label for="forwp_account_log"><?php esc_html_e( 'Email or username', '4wp-account' ); ?></label>
				<input type="text" id="forwp_account_log" name="forwp_account_log" value="<?php echo esc_attr( $login_value ); ?>" autocomplete="username" required>
			</p>
			<p class="forwp-account-password-field">
				<label for="forwp_account_pwd"><?php esc_html_e( 'Password', '4wp-account' ); ?></label>
				<input type="password" id="forwp_account_pwd" name="forwp_account_pwd" autocomplete="current-password" required>
			</p>
			</div>
			<p class="forwp-account-password-row">
				<label class="forwp-account-password-remember">
					<input type="checkbox" name="forwp_account_remember" value="1">
					<?php esc_html_e( 'Remember me', '4wp-account' ); ?>
				</label>
			</p>
			<p class="forwp-account-password-actions">
				<button type="submit" class="forwp-account-password-submit wp-element-button"><?php esc_html_e( 'Sign in', '4wp-account' ); ?></button>
			</p>
		</form>
		<p class="forwp-account-password-links">
			<a href="<?php echo esc_url( add_query_arg( 'form', 'lostpassword', $base_url ) ); ?>"><?php esc_html_e( 'Forgot password?', '4wp-account' ); ?></a>
			<?php if ( self::can_register() ) : ?>
				<span aria-hidden="true">·</span>
				<a href="<?php echo esc_url( add_query_arg( 'form', 'register', $base_url ) ); ?>"><?php esc_html_e( 'Create an account', '4wp-account' ); ?></a>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * @param string $base_url Account page URL.
	 */
	private static function render_register_form( string $base_url ): void {
		$email_value = '';
		if ( isset( $_POST['forwp_account_email'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Repopulate after failed attempt.
			$email_value = sanitize_email( wp_unslash( (string) $_POST['forwp_account_email'] ) );
		}
		?>
		<form class="forwp-account-password-form" method="post" action="<?php echo esc_url( add_query_arg( 'form', 'register', $base_url ) ); ?>">
			<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
			<input type="hidden" name="forwp_account_form_action" value="register">

			<div class="forwp-account-password-fields">
			<p class="forwp-account-password-field">
				<label for="forwp_account_email"><?php esc_html_e( 'Email', '4wp-account' ); ?></label>
				<input type="email" id="forwp_account_email" name="forwp_account_email" value="<?php echo esc_attr( $email_value ); ?>" autocomplete="email" required>
			</p>
			<p class="forwp-account-password-field">
				<label for="forwp_account_new_pwd"><?php esc_html_e( 'Password', '4wp-account' ); ?></label>
				<input type="password" id="forwp_account_new_pwd" name="forwp_account_new_pwd" autocomplete="new-password" required minlength="8">
			</p>
			</div>
			<p class="forwp-account-password-actions">
				<button type="submit" class="forwp-account-password-submit wp-element-button"><?php esc_html_e( 'Create account', '4wp-account' ); ?></button>
			</p>
		</form>
		<p class="forwp-account-password-links">
			<a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Already have an account? Sign in', '4wp-account' ); ?></a>
		</p>
		<?php
	}

	/**
	 * @param string $base_url Account page URL.
	 */
	private static function render_lost_password_form( string $base_url ): void {
		?>
		<form class="forwp-account-password-form" method="post" action="<?php echo esc_url( add_query_arg( 'form', 'lostpassword', $base_url ) ); ?>">
			<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD ); ?>
			<input type="hidden" name="forwp_account_form_action" value="lostpassword">

			<p class="forwp-account-password-field">
				<label for="forwp_account_lost"><?php esc_html_e( 'Email or username', '4wp-account' ); ?></label>
				<input type="text" id="forwp_account_lost" name="forwp_account_lost" autocomplete="username" required>
			</p>
			<p class="forwp-account-password-actions">
				<button type="submit" class="forwp-account-password-submit wp-element-button"><?php esc_html_e( 'Send reset link', '4wp-account' ); ?></button>
			</p>
		</form>
		<p class="forwp-account-password-links">
			<a href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Back to sign in', '4wp-account' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Sign in with username or email.
	 */
	private static function handle_login(): void {
		$login    = isset( $_POST['forwp_account_log'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['forwp_account_log'] ) ) : '';
		$password = isset( $_POST['forwp_account_pwd'] ) ? (string) wp_unslash( $_POST['forwp_account_pwd'] ) : '';
		$remember = ! empty( $_POST['forwp_account_remember'] );

		if ( '' === $login || '' === $password ) {
			self::fail( __( 'Enter your email or username and password.', '4wp-account' ), 'login' );
		}

		if ( is_email( $login ) ) {
			$user_by_email = get_user_by( 'email', $login );
			if ( $user_by_email instanceof \WP_User ) {
				$login = $user_by_email->user_login;
			}
		}

		$result = wp_signon(
			array(
				'user_login'    => $login,
				'user_password' => $password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $result ) ) {
			self::fail( $result->get_error_message(), 'login' );
		}

		self::succeed();
	}

	/**
	 * Create a subscriber and sign them in.
	 */
	private static function handle_register(): void {
		if ( ! self::can_register() ) {
			self::fail( __( 'Registration is disabled.', '4wp-account' ), 'login' );
		}

		$email    = isset( $_POST['forwp_account_email'] ) ? sanitize_email( wp_unslash( (string) $_POST['forwp_account_email'] ) ) : '';
		$password = isset( $_POST['forwp_account_new_pwd'] ) ? (string) wp_unslash( $_POST['forwp_account_new_pwd'] ) : '';

		if ( ! is_email( $email ) ) {
			self::fail( __( 'Enter a valid email address.', '4wp-account' ), 'register' );
		}

		if ( strlen( $password ) < 8 ) {
			self::fail( __( 'Password must be at least 8 characters.', '4wp-account' ), 'register' );
		}

		if ( email_exists( $email ) ) {
			self::fail( __( 'An account with this email already exists. Sign in instead.', '4wp-account' ), 'login' );
		}

		$username = self::username_from_email( $email );
		$user_id  = wp_create_user( $username, $password, $email );

		if ( is_wp_error( $user_id ) ) {
			self::fail( $user_id->get_error_message(), 'register' );
		}

		$result = wp_signon(
			array(
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => true,
			),
			is_ssl()
		);

		if ( is_wp_error( $result ) ) {
			self::fail( $result->get_error_message(), 'login' );
		}

		self::succeed();
	}

	/**
	 * Send the core lost-password email.
	 */
	private static function handle_lost_password(): void {
		$login = isset( $_POST['forwp_account_lost'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['forwp_account_lost'] ) ) : '';

		if ( '' === $login ) {
			self::fail( __( 'Enter your email or username.', '4wp-account' ), 'lostpassword' );
		}

		if ( is_email( $login ) ) {
			$user_by_email = get_user_by( 'email', $login );
			if ( $user_by_email instanceof \WP_User ) {
				$login = $user_by_email->user_login;
			}
		}

		if ( function_exists( 'retrieve_password' ) ) {
			$result = retrieve_password( $login );
			if ( is_wp_error( $result ) ) {
				self::fail( $result->get_error_message(), 'lostpassword' );
			}
		} else {
			self::fail( __( 'Password reset is not available.', '4wp-account' ), 'lostpassword' );
		}

		$url = add_query_arg(
			array(
				'form'                 => 'login',
				'forwp_account_notice' => rawurlencode( __( 'Check your email for the reset link.', '4wp-account' ) ),
			),
			AccountMenu::get_account_page_url()
		);

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Unique username from the email local part.
	 *
	 * @param string $email Email address.
	 */
	private static function username_from_email( string $email ): string {
		$base = sanitize_user( strstr( $email, '@', true ), true );
		if ( '' === $base ) {
			$base = 'user';
		}

		$username = $base;
		$counter  = 1;

		while ( username_exists( $username ) ) {
			$username = $base . $counter;
			++$counter;
		}

		return $username;
	}

	/**
	 * Redirect after successful login.
	 */
	private static function succeed(): void {
		$default = AccountMenu::get_account_page_url();
		$target  = OAuthRedirect::consume_cookie( $default );

		wp_safe_redirect( OAuthRedirect::normalize( $target, $default ) );
		exit;
	}

	/**
	 * PRG error redirect.
	 *
	 * @param string $message Error message.
	 * @param string $mode    Form panel.
	 */
	private static function fail( string $message, string $mode = 'login' ): void {
		$args = array(
			'forwp_account_error' => rawurlencode( $message ),
		);

		if ( 'login' !== $mode ) {
			$args['form'] = $mode;
		}

		wp_safe_redirect( add_query_arg( $args, AccountMenu::get_account_page_url() ) );
		exit;
	}
}
