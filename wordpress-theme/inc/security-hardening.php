<?php
/**
 * Aelyra Oxyne Z — durcissement sécurité.
 *
 * 1. En-têtes HTTP de sécurité (send_headers).
 * 2. XML-RPC désactivé + 403 loggué sur accès direct.
 * 3. Version de WordPress masquée (wp_generator).
 * 4. Honeypot anti-bot sur connexion et inscription.
 * 5. Blocage temporaire d'IP après 10 échecs de connexion (1 heure).
 * 6. Blocage de l'énumération d'utilisateurs (?author=N + API REST).
 * 7. Détection des requêtes suspectes (template_redirect).
 *
 * Conventions du thème : garde ABSPATH partout, $wpdb->prepare(),
 * esc_* en sortie, journalisation via axz_sec_log() (garde
 * function_exists, au cas où le module de journal serait absent).
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
   1. En-têtes HTTP de sécurité.
   Pas de Content-Security-Policy bloquante : elle casserait les
   scripts et styles légitimes du site et de l'admin.
------------------------------------------------------------------ */
function axz_security_headers() {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
add_action( 'send_headers', 'axz_security_headers' );

/* ------------------------------------------------------------------
   3. Masquer la version de WordPress (moins d'infos pour les bots
   qui ciblent les vieilles versions).
------------------------------------------------------------------ */
remove_action( 'wp_head', 'wp_generator' );

/* ------------------------------------------------------------------
   2. XML-RPC désactivé : vecteur classique d'attaques par force brute
   et de pingbacks abusifs. Accès direct = 403 + log.
------------------------------------------------------------------ */
add_filter( 'xmlrpc_enabled', '__return_false' );

function axz_block_xmlrpc_direct() {
	$script = isset( $_SERVER['SCRIPT_NAME'] ) ? wp_unslash( $_SERVER['SCRIPT_NAME'] ) : '';
	if ( is_string( $script ) && false !== strpos( $script, 'xmlrpc.php' ) ) {
		if ( function_exists( 'axz_sec_log' ) ) {
			axz_sec_log( 'xmlrpc_bloque', 'xmlrpc.php', __( 'Accès direct à xmlrpc.php bloqué.', 'axz' ) );
		}
		wp_die( esc_html__( 'Accès interdit.', 'axz' ), '', array( 'response' => 403 ) );
	}
}
add_action( 'init', 'axz_block_xmlrpc_direct', 1 );

/* ------------------------------------------------------------------
   4. Honeypot anti-bot.
   Champ « axz_website » invisible en CSS : un humain ne le voit pas,
   un bot le remplit. Si rempli → log bot_honeypot + 403 générique
   (on ne dit jamais au bot qu'il a été détecté comme tel).
------------------------------------------------------------------ */
function axz_honeypot_field() {
	echo '<p class="axz-hp" aria-hidden="true"><label>' . esc_html__( 'Site web', 'axz' ) . ' <input type="text" name="axz_website" value="" tabindex="-1" autocomplete="off"></label></p>';
}
/* Injecté dans wp-login.php ET dans wp_login_form() (modèle Connexion). */
add_action( 'login_form', 'axz_honeypot_field' );

function axz_honeypot_style_login() {
	echo '<style>.axz-hp{position:absolute!important;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;}</style>';
}
add_action( 'login_head', 'axz_honeypot_style_login' );

/* Vérifié AVANT l'authentification, sur tous les formulaires de login. */
function axz_honeypot_check_login( $user, $username, $password ) {
	if ( $user instanceof WP_User ) {
		return $user;
	}
	if ( isset( $_POST['axz_website'] ) && '' !== trim( (string) wp_unslash( $_POST['axz_website'] ) ) ) {
		if ( function_exists( 'axz_sec_log' ) ) {
			axz_sec_log( 'bot_honeypot', 'connexion', __( 'Honeypot rempli sur le formulaire de connexion.', 'axz' ) );
		}
		wp_die( esc_html__( 'Une erreur est survenue. Réessaie.', 'axz' ), '', array( 'response' => 403 ) );
	}
	return $user;
}
add_filter( 'authenticate', 'axz_honeypot_check_login', 5, 3 );

/* ------------------------------------------------------------------
   5. Blocage temporaire d'IP : 10 échecs de connexion en 1 heure
   → IP bloquée 1 heure (option axz_ip_bloquees : ip => fin du blocage).
   Complète la limite existante (5 essais / 15 min, inc/security.php).
------------------------------------------------------------------ */
function axz_handle_login_fail_block( $username ) {
	if ( function_exists( 'axz_sec_log' ) ) {
		$cible = is_string( $username ) ? mb_substr( $username, 0, 191 ) : '';
		axz_sec_log( 'connexion_echouee', $cible, '' );
	}
	$ip = function_exists( 'axz_sec_ip' ) ? axz_sec_ip() : 'unknown';
	if ( 'unknown' === $ip ) {
		return;
	}
	$c = get_option( 'axz_compteur_echecs', array() );
	if ( ! is_array( $c ) ) {
		$c = array();
	}
	$now = time();
	if ( ! isset( $c[ $ip ] ) || ! is_array( $c[ $ip ] ) || $now - (int) $c[ $ip ]['debut'] > HOUR_IN_SECONDS ) {
		$c[ $ip ] = array( 'n' => 0, 'debut' => $now );
	}
	$c[ $ip ]['n']++;
	if ( $c[ $ip ]['n'] >= 10 ) {
		$bl = get_option( 'axz_ip_bloquees', array() );
		if ( ! is_array( $bl ) ) {
			$bl = array();
		}
		$bl[ $ip ] = $now + HOUR_IN_SECONDS;
		update_option( 'axz_ip_bloquees', $bl );
		unset( $c[ $ip ] );
		if ( function_exists( 'axz_sec_log' ) ) {
			axz_sec_log( 'ip_bloquee', $ip, __( 'Blocage automatique : 10 échecs de connexion en 1 heure.', 'axz' ) );
		}
	}
	update_option( 'axz_compteur_echecs', $c );
}
add_action( 'wp_login_failed', 'axz_handle_login_fail_block' );

/* Application du blocage : toute requête d'une IP bloquée = 403 + log.
   Les blocages expirés sont nettoyés au passage. */
function axz_enforce_ip_block() {
	if ( ! function_exists( 'axz_sec_ip' ) ) {
		return;
	}
	$ip = axz_sec_ip();
	if ( 'unknown' === $ip ) {
		return;
	}
	$bl = get_option( 'axz_ip_bloquees', array() );
	if ( ! is_array( $bl ) || ! isset( $bl[ $ip ] ) ) {
		return;
	}
	if ( time() >= (int) $bl[ $ip ] ) {
		unset( $bl[ $ip ] );
		update_option( 'axz_ip_bloquees', $bl );
		return;
	}
	if ( function_exists( 'axz_sec_log' ) ) {
		axz_sec_log( 'ip_bloquee', $ip, __( 'Tentative d\u2019accès depuis une IP bloquée.', 'axz' ) );
	}
	wp_die( esc_html__( 'Accès temporairement bloqué. Réessaie plus tard.', 'axz' ), '', array( 'response' => 403 ) );
}
add_action( 'init', 'axz_enforce_ip_block', 1 );

/* ------------------------------------------------------------------
   6. Énumération d'utilisateurs.
   a) ?author=N → redirection vers l'accueil + log (pas de 403 : on ne
      révèle pas au bot qu'il a touché un mécanisme de défense).
   b) API REST /wp/v2/users masquée pour les visiteurs non connectés.
------------------------------------------------------------------ */
function axz_block_author_enum() {
	if ( is_admin() ) {
		return;
	}
	$author = isset( $_GET['author'] ) ? absint( $_GET['author'] ) : 0;
	if ( $author > 0 ) {
		if ( function_exists( 'axz_sec_log' ) ) {
			axz_sec_log( 'requete_suspecte', '?author=' . $author, __( 'Tentative d\u2019énumération d\u2019utilisateurs.', 'axz' ) );
		}
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'axz_block_author_enum', 1 );

function axz_restrict_rest_users( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'] );
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'axz_restrict_rest_users' );

/* ------------------------------------------------------------------
   7. Requêtes suspectes (façade publique uniquement : template_redirect
   ne s'exécute pas dans l'admin).
   - BLOQUÉES en 403 (motifs blatants) : ../, ..\, wp-config, .env, .git
   - LOGGUÉES seulement (motifs suspects) : <script, UNION SELECT, eval(
------------------------------------------------------------------ */
function axz_scan_requete_suspecte() {
	if ( is_admin() ) {
		return;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	if ( ! is_string( $uri ) || '' === $uri ) {
		return;
	}
	$blatants = array( '../', '..\\', 'wp-config', '.env', '.git' );
	foreach ( $blatants as $motif ) {
		if ( false !== stripos( $uri, $motif ) ) {
			if ( function_exists( 'axz_sec_log' ) ) {
				axz_sec_log( 'requete_suspecte', mb_substr( $uri, 0, 191 ), sprintf( __( 'Motif bloqué : %s', 'axz' ), $motif ) );
			}
			wp_die( esc_html__( 'Requête bloquée.', 'axz' ), '', array( 'response' => 403 ) );
		}
	}
	$suspects = array( '<script', 'union select', 'eval(' );
	foreach ( $suspects as $motif ) {
		if ( false !== stripos( $uri, $motif ) ) {
			if ( function_exists( 'axz_sec_log' ) ) {
				axz_sec_log( 'requete_suspecte', mb_substr( $uri, 0, 191 ), sprintf( __( 'Motif suspect : %s', 'axz' ), $motif ) );
			}
			break;
		}
	}
}
add_action( 'template_redirect', 'axz_scan_requete_suspecte', 1 );
