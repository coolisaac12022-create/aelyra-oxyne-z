<?php
/**
 * Aelyra Oxyne Z — sécurité.
 *
 * 1. Limitation des tentatives de connexion : 5 essais / 15 min par IP.
 * 2. Les non-administrateurs sont redirigés hors de wp-admin.
 * 3. La barre d'admin est masquée pour les clients.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
   1. Limitation des tentatives de connexion (anti force brute)
------------------------------------------------------------------ */
function axz_login_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		$ip = 'unknown';
	}
	return $ip;
}

function axz_login_attempt_key() {
	return 'axz_login_' . md5( axz_login_ip() );
}

/* Vérifié AVANT l'authentification : si le quota est dépassé, on bloque. */
function axz_check_login_limit( $user, $username, $password ) {
	if ( $user instanceof WP_User ) {
		return $user;
	}
	$count = (int) get_transient( axz_login_attempt_key() );
	if ( $count >= 5 ) {
		return new WP_Error(
			'axz_limite',
			__( 'Trop de tentatives de connexion. Réessaie dans 15 minutes.', 'axz' )
		);
	}
	return $user;
}
add_filter( 'authenticate', 'axz_check_login_limit', 30, 3 );

/* Chaque échec incrémente le compteur (15 minutes glissantes). */
function axz_record_login_fail( $username ) {
	$key   = axz_login_attempt_key();
	$count = (int) get_transient( $key );
	set_transient( $key, $count + 1, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'axz_record_login_fail' );

/* Une connexion réussie remet le compteur à zéro. */
function axz_clear_login_fail( $user_login, $user ) {
	delete_transient( axz_login_attempt_key() );
}
add_action( 'wp_login', 'axz_clear_login_fail', 10, 2 );

/* ------------------------------------------------------------------
   2. Hors de wp-admin pour les non-administrateurs
   (les clients gèrent tout depuis leur espace client public)
------------------------------------------------------------------ */
function axz_redirect_non_admins() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	if ( ! is_user_logged_in() ) {
		return;
	}
	if ( current_user_can( 'manage_options' ) ) {
		return;
	}
	wp_safe_redirect( home_url( '/espace-client/' ) );
	exit;
}
add_action( 'admin_init', 'axz_redirect_non_admins' );

/* ------------------------------------------------------------------
   3. Barre d'admin masquée pour les clients
------------------------------------------------------------------ */
function axz_hide_admin_bar( $show ) {
	if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
		return false;
	}
	return $show;
}
add_filter( 'show_admin_bar', 'axz_hide_admin_bar' );
