<?php
/**
 * Aelyra Oxyne Z — journal de sécurité.
 *
 * Table {prefix}axz_securite : chaque tentative d'attaque y est enregistrée
 * (date, IP, navigateur, type, cible, détails). L'IP sert de preuve pour les
 * autorités et l'hébergeur — elle ne permet PAS d'identifier la personne
 * derrière l'attaque (VPN, proxy, IP partagée). Pas de « hack back ».
 *
 * Types : connexion_echouee, acces_interdit, nonce_invalide, bot_honeypot,
 *         requete_suspecte, ip_bloquee, xmlrpc_bloque.
 *
 * Conventions du thème : garde ABSPATH partout, current_user_can() sur
 * chaque page et chaque action, nonces sur tout changement d'état,
 * $wpdb->prepare() pour le SQL, esc_* en sortie.
 */
defined( 'ABSPATH' ) || exit;

define( 'AXZ_SECURITE_DB_VERSION', '1.0' );
define( 'AXZ_SECURITE_RETENTION_JOURS', 90 );

/* ------------------------------------------------------------------
   IP du visiteur — REMOTE_ADDR UNIQUEMENT.
   On ignore volontairement X-Forwarded-For / CF-Connecting-IP : ces
   en-têtes sont forgés par le client et un attaquant pourrait y mettre
   l'IP d'un innocent pour le faire bloquer (risque de spoof).
------------------------------------------------------------------ */
function axz_sec_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$ip = is_string( $ip ) ? trim( $ip ) : '';
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		return 'unknown';
	}
	return $ip;
}

function axz_sec_types() {
	return array(
		'connexion_echouee',
		'acces_interdit',
		'nonce_invalide',
		'bot_honeypot',
		'requete_suspecte',
		'ip_bloquee',
		'xmlrpc_bloque',
	);
}

function axz_securite_table() {
	global $wpdb;
	return $wpdb->prefix . 'axz_securite';
}

function axz_create_securite_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = axz_securite_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		ip varchar(45) NOT NULL DEFAULT '',
		user_agent varchar(255) NOT NULL DEFAULT '',
		type varchar(32) NOT NULL DEFAULT '',
		cible varchar(191) NOT NULL DEFAULT '',
		details text NOT NULL,
		PRIMARY KEY  (id),
		KEY ip (ip),
		KEY type (type),
		KEY date (date)
	) $charset;";
	dbDelta( $sql );
	update_option( 'axz_securite_db_version', AXZ_SECURITE_DB_VERSION );
}
add_action( 'after_switch_theme', 'axz_create_securite_table' );

/* Filet : recrée la table si la version n'est pas à jour. */
function axz_maybe_create_securite_table() {
	if ( get_option( 'axz_securite_db_version' ) !== AXZ_SECURITE_DB_VERSION ) {
		axz_create_securite_table();
	}
}
add_action( 'admin_init', 'axz_maybe_create_securite_table' );

/* Vérifie (une fois par requête) que la table existe avant d'écrire.
   Évite un échec silencieux si l'activation n'a pas créé la table. */
function axz_sec_table_ok() {
	static $ok = null;
	if ( null === $ok ) {
		global $wpdb;
		$ok = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', axz_securite_table() ) );
		if ( ! $ok ) {
			axz_create_securite_table();
			$ok = (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', axz_securite_table() ) );
		}
	}
	return $ok;
}

/**
 * Écrit un événement de sécurité. $cible = ce qui était visé
 * (nom d'utilisateur, URL, action...), $details = précision libre.
 */
function axz_sec_log( $type, $cible = '', $details = '' ) {
	global $wpdb;
	if ( ! in_array( $type, axz_sec_types(), true ) ) {
		$type = 'requete_suspecte';
	}
	if ( ! axz_sec_table_ok() ) {
		return 0;
	}
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
	$ua = is_string( $ua ) ? mb_substr( $ua, 0, 255 ) : '';
	$cible   = is_string( $cible ) ? mb_substr( $cible, 0, 191 ) : '';
	$details = is_string( $details ) ? $details : '';
	$result  = $wpdb->insert(
		axz_securite_table(),
		array(
			'date'       => current_time( 'mysql' ),
			'ip'         => axz_sec_ip(),
			'user_agent' => $ua,
			'type'       => $type,
			'cible'      => $cible,
			'details'    => $details,
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s' )
	);
	if ( $result ) {
		axz_sec_maybe_alert( $type );
		return (int) $wpdb->insert_id;
	}
	return 0;
}

/* Alerte e-mail : une IP qui génère 10 événements en 1h = suspect.
   Envoi UNIQUE (quand le compteur atteint exactement 10) pour ne pas
   spammer. L'envoi dépend de l'hébergeur : le journal admin reste la
   source fiable, le mail n'est qu'un bonus. */
function axz_sec_maybe_alert( $type ) {
	if ( ! in_array( $type, array( 'connexion_echouee', 'requete_suspecte', 'bot_honeypot', 'nonce_invalide' ), true ) ) {
		return;
	}
	global $wpdb;
	$table = axz_securite_table();
	$ip    = axz_sec_ip();
	$since = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - HOUR_IN_SECONDS );
	$n     = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM $table WHERE ip = %s AND date >= %s",
		$ip,
		$since
	) );
	if ( 10 !== $n ) {
		return;
	}
	$admin = get_option( 'admin_email' );
	if ( ! $admin || ! is_email( $admin ) ) {
		return;
	}
	wp_mail(
		$admin,
		'[Aelyra] Activité suspecte détectée',
		sprintf(
			"L'adresse IP %s a généré %d événements de sécurité en une heure sur %s.\n\nConsulte le journal : %s\n\nNote : l'envoi d'e-mails dépend de l'hébergeur — le journal « Sécurité » du tableau Aelyra reste la source fiable.",
			$ip,
			$n,
			home_url( '/' ),
			admin_url( 'admin.php?page=axz-securite' )
		)
	);
}

/* ------------------------------------------------------------------
   Nonce avec journalisation : remplace check_admin_referer() dans les
   handlers. Même comportement (wp_die 403 en cas d'échec), PLUS une
   ligne nonce_invalide dans le journal de sécurité.
------------------------------------------------------------------ */
function axz_verify_nonce_log( $action, $field = '_wpnonce' ) {
	$nonce = isset( $_REQUEST[ $field ] ) ? wp_unslash( $_REQUEST[ $field ] ) : '';
	if ( ! is_string( $nonce ) || '' === $nonce || ! wp_verify_nonce( $nonce, $action ) ) {
		axz_sec_log( 'nonce_invalide', $action, __( 'Nonce invalide ou manquant.', 'axz' ) );
		wp_die( esc_html__( 'Session expirée. Recharge la page et réessaie.', 'axz' ), '', array( 'response' => 403 ) );
	}
	return true;
}

/* ------------------------------------------------------------------
   Purge automatique : conservation 90 jours (cron quotidien).
------------------------------------------------------------------ */
function axz_schedule_purge_securite() {
	if ( ! wp_next_scheduled( 'axz_purge_securite' ) ) {
		wp_schedule_event( time(), 'daily', 'axz_purge_securite' );
	}
}
add_action( 'after_switch_theme', 'axz_schedule_purge_securite' );

function axz_purge_securite() {
	global $wpdb;
	$table = axz_securite_table();
	$limit = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - AXZ_SECURITE_RETENTION_JOURS * DAY_IN_SECONDS );
	$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE date < %s", $limit ) );
	/* Ménage : on oublie les blocages expirés depuis plus de 24h. */
	$bl = get_option( 'axz_ip_bloquees', array() );
	if ( is_array( $bl ) ) {
		$changed = false;
		foreach ( $bl as $ip => $fin ) {
			if ( time() - (int) $fin > DAY_IN_SECONDS ) {
				unset( $bl[ $ip ] );
				$changed = true;
			}
		}
		if ( $changed ) {
			update_option( 'axz_ip_bloquees', $bl );
		}
	}
}
add_action( 'axz_purge_securite', 'axz_purge_securite' );

/* ------------------------------------------------------------------
   Menu : page « Sécurité » (capacité axz_manage_clients)
------------------------------------------------------------------ */
function axz_admin_menu_securite() {
	add_submenu_page(
		'axz-dashboard',
		__( 'Sécurité', 'axz' ),
		__( 'Sécurité', 'axz' ),
		'axz_manage_clients',
		'axz-securite',
		'axz_page_securite'
	);
}
add_action( 'admin_menu', 'axz_admin_menu_securite' );

/* Blocage / déblocage manuel d'une IP (nonce + capacité). */
function axz_handle_ip_block() {
	if ( ! isset( $_POST['axz_ip_action'] ) ) {
		return;
	}
	axz_verify_nonce_log( 'axz_ip_block', 'axz_ip_nonce' );
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		axz_sec_log( 'acces_interdit', 'axz-securite', 'Blocage/déblocage IP sans la capacité requise.' );
		wp_die( esc_html__( 'Accès refusé.', 'axz' ), '', array( 'response' => 403 ) );
	}
	$ip = isset( $_POST['axz_ip'] ) ? trim( (string) wp_unslash( $_POST['axz_ip'] ) ) : '';
	if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		wp_die( esc_html__( 'Adresse IP invalide.', 'axz' ), '', array( 'response' => 400 ) );
	}
	$action = sanitize_key( wp_unslash( $_POST['axz_ip_action'] ) );
	if ( ! in_array( $action, array( 'bloquer', 'debloquer' ), true ) ) {
		wp_die( esc_html__( 'Action inconnue.', 'axz' ), '', array( 'response' => 400 ) );
	}
	$bl = get_option( 'axz_ip_bloquees', array() );
	if ( ! is_array( $bl ) ) {
		$bl = array();
	}
	if ( 'bloquer' === $action ) {
		$bl[ $ip ] = time() + HOUR_IN_SECONDS;
		axz_sec_log( 'ip_bloquee', $ip, 'Blocage manuel depuis le journal de sécurité (1 heure).' );
	} else {
		unset( $bl[ $ip ] );
		axz_sec_log( 'ip_bloquee', $ip, 'Déblocage manuel depuis le journal de sécurité.' );
	}
	update_option( 'axz_ip_bloquees', $bl );
	wp_safe_redirect( add_query_arg(
		array( 'page' => 'axz-securite', 'axz_msg' => 'ok' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_init', 'axz_handle_ip_block' );

function axz_sec_type_label( $type ) {
	$map = array(
		'connexion_echouee' => __( 'Connexion échouée', 'axz' ),
		'acces_interdit'    => __( 'Accès interdit', 'axz' ),
		'nonce_invalide'    => __( 'Nonce invalide', 'axz' ),
		'bot_honeypot'      => __( 'Bot (honeypot)', 'axz' ),
		'requete_suspecte'  => __( 'Requête suspecte', 'axz' ),
		'ip_bloquee'        => __( 'IP bloquée', 'axz' ),
		'xmlrpc_bloque'     => __( 'XML-RPC bloqué', 'axz' ),
	);
	return isset( $map[ $type ] ) ? $map[ $type ] : $type;
}

function axz_page_securite() {
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ), '', array( 'response' => 403 ) );
	}
	global $wpdb;
	$table    = axz_securite_table();
	$per_page = 20;
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$offset   = ( $paged - 1 ) * $per_page;

	$type_filter = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
	if ( '' !== $type_filter && ! in_array( $type_filter, axz_sec_types(), true ) ) {
		$type_filter = '';
	}
	$ip_raw    = isset( $_GET['ip'] ) ? trim( (string) wp_unslash( $_GET['ip'] ) ) : '';
	$ip_filter = filter_var( $ip_raw, FILTER_VALIDATE_IP ) ? $ip_raw : '';

	$where  = array();
	$params = array();
	if ( '' !== $type_filter ) {
		$where[]  = 'type = %s';
		$params[] = $type_filter;
	}
	if ( '' !== $ip_filter ) {
		$where[]  = 'ip = %s';
		$params[] = $ip_filter;
	}
	$where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

	$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table $where_sql", $params ) );
	$rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM $table $where_sql ORDER BY date DESC LIMIT %d OFFSET %d",
		array_merge( $params, array( $per_page, $offset ) )
	) );

	/* Compteurs par type. */
	$compteurs = array();
	$res       = $wpdb->get_results( "SELECT type, COUNT(*) AS n FROM $table GROUP BY type" );
	foreach ( $res as $r ) {
		$compteurs[ $r->type ] = (int) $r->n;
	}

	$bloquees = get_option( 'axz_ip_bloquees', array() );
	if ( ! is_array( $bloquees ) ) {
		$bloquees = array();
	}
	$pages = max( 1, (int) ceil( $total / $per_page ) );

	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Blocage mis à jour.', 'axz' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Journal de sécurité', 'axz' ); ?></h1>
		<div class="axz-note">
			<?php esc_html_e( 'Chaque tentative d\u2019attaque est enregistrée : date, adresse IP, navigateur. Les IP servent de preuves pour les autorités ou l\u2019hébergeur — elles ne permettent pas d\u2019identifier qui se cache derrière (VPN, proxy). Conservation : 90 jours.', 'axz' ); ?>
			<br><?php esc_html_e( 'Alerte e-mail : si une IP génère 10 événements en 1 heure, l\u2019administrateur reçoit un e-mail. L\u2019envoi dépend de l\u2019hébergeur : ce journal reste la source fiable.', 'axz' ); ?>
		</div>

		<div class="axz-stats">
			<?php foreach ( axz_sec_types() as $t ) : ?>
				<div class="axz-stat">
					<div class="s-tag"><?php echo esc_html( mb_strtoupper( axz_sec_type_label( $t ) ) ); ?></div>
					<div class="s-val"><?php echo (int) ( $compteurs[ $t ] ?? 0 ); ?></div>
					<div class="s-sub"><?php esc_html_e( 'événements enregistrés', 'axz' ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $bloquees ) ) : ?>
			<div class="axz-alert-box">
				<div class="s-tag"><?php esc_html_e( 'IP ACTUELLEMENT BLOQUÉES', 'axz' ); ?></div>
				<ul>
					<?php foreach ( $bloquees as $bip => $fin ) : ?>
						<li>
							<code><?php echo esc_html( $bip ); ?></code>
							—
							<?php
							/* translators: %s = date de fin du blocage */
							printf( esc_html__( 'bloquée jusqu\u2019à %s', 'axz' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $fin ) ) );
							?>
							<form method="post" class="axz-rowform" style="display:inline;margin-left:10px">
								<?php wp_nonce_field( 'axz_ip_block', 'axz_ip_nonce' ); ?>
								<input type="hidden" name="axz_ip" value="<?php echo esc_attr( $bip ); ?>">
								<input type="hidden" name="axz_ip_action" value="debloquer">
								<button type="submit" class="button"><?php esc_html_e( 'Débloquer', 'axz' ); ?></button>
							</form>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<form method="get" class="axz-rowform" style="margin-bottom:16px">
			<input type="hidden" name="page" value="axz-securite">
			<label for="axz-type-filter"><?php esc_html_e( 'Type :', 'axz' ); ?></label>
			<select name="type" id="axz-type-filter">
				<option value=""><?php esc_html_e( 'Tous', 'axz' ); ?></option>
				<?php foreach ( axz_sec_types() as $t ) : ?>
					<option value="<?php echo esc_attr( $t ); ?>" <?php selected( $type_filter, $t ); ?>>
						<?php echo esc_html( axz_sec_type_label( $t ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<label for="axz-ip-filter"><?php esc_html_e( 'IP :', 'axz' ); ?></label>
			<input type="text" name="ip" id="axz-ip-filter" value="<?php echo esc_attr( $ip_filter ); ?>" placeholder="1.2.3.4" class="regular-text" style="max-width:160px">
			<button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'axz' ); ?></button>
		</form>

		<?php if ( empty( $rows ) ) : ?>
			<div class="axz-note"><?php esc_html_e( 'Aucun événement de sécurité enregistré. C\u2019est bon signe.', 'axz' ); ?></div>
		<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'axz' ); ?></th>
					<th><?php esc_html_e( 'IP', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Type', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Cible', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Navigateur', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Détails', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Action', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->date ) ) ); ?></td>
						<td><code><?php echo esc_html( $r->ip ); ?></code></td>
						<td><span class="axz-badge sec-<?php echo esc_attr( $r->type ); ?>"><?php echo esc_html( axz_sec_type_label( $r->type ) ); ?></span></td>
						<td><?php echo esc_html( $r->cible ); ?></td>
						<td style="max-width:220px;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html( $r->user_agent ); ?></td>
						<td><?php echo esc_html( $r->details ); ?></td>
						<td>
							<?php if ( 'unknown' !== $r->ip && ! isset( $bloquees[ $r->ip ] ) ) : ?>
								<form method="post" class="axz-rowform">
									<?php wp_nonce_field( 'axz_ip_block', 'axz_ip_nonce' ); ?>
									<input type="hidden" name="axz_ip" value="<?php echo esc_attr( $r->ip ); ?>">
									<input type="hidden" name="axz_ip_action" value="bloquer">
									<button type="submit" class="button" style="color:#b32d2e"><?php esc_html_e( 'Bloquer', 'axz' ); ?></button>
								</form>
							<?php else : ?>
								<span style="color:#999">—</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo paginate_links( array(
					'base'    => add_query_arg( array( 'paged' => '%#%', 'type' => $type_filter, 'ip' => $ip_filter ) ),
					'format'  => '',
					'current' => $paged,
					'total'   => $pages,
				) );
				?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php
}
