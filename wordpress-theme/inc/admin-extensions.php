<?php
/**
 * Aelyra Oxyne Z — extensions d'administration.
 *
 * 1. Essais à relancer (axz_manage_clients) : essais finissant dans ≤ 3 jours,
 *    bouton « Marquer relancé ».
 * 2. Paiements (axz_manage_subscriptions) : statut manuel par client
 *    (a_jour / en_attente) — pas de prélèvement automatique Mobile Money.
 * 3. Plafonds de coût : réglages + widget d'alerte sur la vue d'ensemble.
 * 4. Journal d'activité : table dédiée (dbDelta), axz_log(), page paginée
 *    avec filtre par client.
 * 5. Alertes Oxyne Z : structure prête, validation/rejet, état vide honnête.
 * 6. Réglages (manage_options) : clés API (masquées), plafonds, général.
 *
 * Conventions du thème : garde ABSPATH partout, current_user_can() sur
 * chaque page et chaque action, nonces sur tout changement d'état,
 * $wpdb->prepare() pour le SQL, esc_* en sortie, données en user_meta.
 */
defined( 'ABSPATH' ) || exit;

define( 'AXZ_JOURNAL_DB_VERSION', '1.0' );

/* ------------------------------------------------------------------
   Options : lecture centralisée (option unique 'axz_options')
------------------------------------------------------------------ */
function axz_get_option( $key, $default = '' ) {
	$opts = get_option( 'axz_options', array() );
	if ( ! is_array( $opts ) || ! array_key_exists( $key, $opts ) ) {
		return $default;
	}
	return $opts[ $key ];
}

/* ------------------------------------------------------------------
   Menu : nouvelles sous-pages
------------------------------------------------------------------ */
function axz_admin_menu_extensions() {
	add_submenu_page(
		'axz-dashboard',
		__( 'Essais à relancer', 'axz' ),
		__( 'Essais', 'axz' ),
		'axz_manage_clients',
		'axz-essais',
		'axz_page_essais'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Paiements', 'axz' ),
		__( 'Paiements', 'axz' ),
		'axz_manage_subscriptions',
		'axz-paiements',
		'axz_page_paiements'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Alertes Oxyne Z', 'axz' ),
		__( 'Alertes Oxyne Z', 'axz' ),
		'axz_manage_clients',
		'axz-alertes-oxyne',
		'axz_page_alertes_oxyne'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Journal d\u2019activité', 'axz' ),
		__( 'Journal', 'axz' ),
		'axz_manage_clients',
		'axz-journal',
		'axz_page_journal'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Réglages Aelyra', 'axz' ),
		__( 'Réglages', 'axz' ),
		'manage_options',
		'axz-reglages',
		'axz_page_reglages'
	);
}
add_action( 'admin_menu', 'axz_admin_menu_extensions' );

/* ------------------------------------------------------------------
   4. Journal d'activité — table dédiée
------------------------------------------------------------------ */
function axz_journal_table() {
	global $wpdb;
	return $wpdb->prefix . 'axz_journal';
}

function axz_create_journal_table() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = axz_journal_table();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE $table (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		auteur_id bigint(20) unsigned NOT NULL DEFAULT 0,
		client_id bigint(20) unsigned NOT NULL DEFAULT 0,
		action varchar(64) NOT NULL DEFAULT '',
		details text NOT NULL,
		PRIMARY KEY  (id),
		KEY client_id (client_id),
		KEY action (action)
	) $charset;";
	dbDelta( $sql );
	update_option( 'axz_journal_db_version', AXZ_JOURNAL_DB_VERSION );
}
add_action( 'after_switch_theme', 'axz_create_journal_table' );

/* Filet : recrée la table si la version n'est pas à jour. */
function axz_maybe_create_journal_table() {
	if ( get_option( 'axz_journal_db_version' ) !== AXZ_JOURNAL_DB_VERSION ) {
		axz_create_journal_table();
	}
}
add_action( 'admin_init', 'axz_maybe_create_journal_table' );

/**
 * Écrit une ligne de journal. $client_id = 0 si l'action ne concerne
 * aucun client précis. Retourne l'ID inséré, 0 en cas d'échec.
 */
function axz_log( $client_id, $action, $details = '' ) {
	global $wpdb;
	$client_id = absint( $client_id );
	$action    = sanitize_key( $action );
	$details   = is_string( $details ) ? $details : '';
	$result    = $wpdb->insert(
		axz_journal_table(),
		array(
			'date'      => current_time( 'mysql' ),
			'auteur_id' => get_current_user_id(),
			'client_id' => $client_id,
			'action'    => $action,
			'details'   => $details,
		),
		array( '%s', '%d', '%d', '%s', '%s' )
	);
	return $result ? (int) $wpdb->insert_id : 0;
}

/* Libellés d'affichage des actions (liste blanche, repli = clé brute). */
function axz_action_label( $action ) {
	$map = array(
		'client_valide'        => __( 'Client validé', 'axz' ),
		'client_suspendu'      => __( 'Client suspendu', 'axz' ),
		'client_reactive'      => __( 'Client réactivé', 'axz' ),
		'abonnement_modifie'   => __( 'Abonnement modifié', 'axz' ),
		'paiement_a_jour'      => __( 'Paiement marqué « payé »', 'axz' ),
		'paiement_en_attente'  => __( 'Paiement marqué « en attente »', 'axz' ),
		'essai_relance'        => __( 'Essai relancé', 'axz' ),
		'oxyne_alerte_validee' => __( 'Alerte Oxyne Z validée', 'axz' ),
		'oxyne_alerte_rejetee' => __( 'Alerte Oxyne Z rejetée', 'axz' ),
	);
	return isset( $map[ $action ] ) ? $map[ $action ] : $action;
}

/* ------------------------------------------------------------------
   1. Essais à relancer — fin d'essai dans ≤ 3 jours
------------------------------------------------------------------ */
function axz_page_essais() {
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}

	/* Traitement : « Marquer relancé ». */
	if ( isset( $_POST['axz_relance_submit'] ) ) {
		/* Nonce vérifié à la main : un échec est journalisé (nonce_invalide). */
		axz_verify_nonce_log( 'axz_relance', 'axz_relance_nonce' );
		if ( ! current_user_can( 'axz_manage_clients' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
		}
		$user_id = isset( $_POST['axz_user_id'] ) ? absint( $_POST['axz_user_id'] ) : 0;
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user || ! in_array( 'client_aelyra', (array) $user->roles, true ) ) {
			wp_die( esc_html__( 'Client introuvable.', 'axz' ) );
		}
		update_user_meta( $user_id, 'axz_relance', gmdate( 'Y-m-d' ) );
		axz_log( $user_id, 'essai_relance', __( 'Relance commerciale effectuée avant fin d\u2019essai.', 'axz' ) );
		wp_safe_redirect( add_query_arg(
			array( 'page' => 'axz-essais', 'axz_msg' => 'ok' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Relance enregistrée.', 'axz' ) . '</p></div>';
	}

	$query = axz_get_clients( array( 'number' => 500, 'fields' => 'all' ) );
	$rows  = array();
	foreach ( $query->get_results() as $user ) {
		if ( 'essai' !== axz_client_statut( $user->ID ) ) {
			continue;
		}
		$fin = get_user_meta( $user->ID, 'axz_fin_essai', true );
		if ( ! $fin ) {
			continue;
		}
		$jours = (int) floor( ( strtotime( $fin . ' 23:59:59' ) - time() ) / DAY_IN_SECONDS );
		if ( $jours > 3 ) {
			continue;
		}
		$rows[] = array(
			'user'    => $user,
			'fin'     => $fin,
			'jours'   => $jours,
			'relance' => get_user_meta( $user->ID, 'axz_relance', true ),
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Essais à relancer', 'axz' ); ?></h1>
		<p><?php esc_html_e( 'Les essais gratuits qui se terminent dans 3 jours ou moins. C\u2019est ici que se joue la conversion en abonnement payant.', 'axz' ); ?></p>
		<?php if ( empty( $rows ) ) : ?>
			<div class="axz-note"><?php esc_html_e( 'Aucun essai en fin de course. Rien à relancer pour le moment.', 'axz' ); ?></div>
		<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Client', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Fin d\u2019essai', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Jours restants', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Relancé le', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Action', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $row['user']->display_name ); ?></strong><br><span style="color:#777"><?php echo esc_html( $row['user']->user_email ); ?></span></td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row['fin'] ) ) ); ?></td>
						<td>
							<?php if ( $row['jours'] < 0 ) : ?>
								<span class="axz-badge suspendu"><?php esc_html_e( 'Expiré', 'axz' ); ?></span>
							<?php elseif ( 0 === $row['jours'] ) : ?>
								<span class="axz-badge essai"><?php esc_html_e( 'Aujourd\u2019hui', 'axz' ); ?></span>
							<?php else : ?>
								<?php echo (int) $row['jours']; ?>
							<?php endif; ?>
						</td>
						<td><?php echo $row['relance'] ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row['relance'] ) ) ) : '<span style="color:#999">—</span>'; ?></td>
						<td>
							<form method="post" class="axz-rowform">
								<?php wp_nonce_field( 'axz_relance', 'axz_relance_nonce' ); ?>
								<input type="hidden" name="axz_user_id" value="<?php echo (int) $row['user']->ID; ?>">
								<button type="submit" name="axz_relance_submit" class="button"><?php esc_html_e( 'Marquer relancé', 'axz' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   2. Paiements — statut manuel par client
------------------------------------------------------------------ */
function axz_paiement_statut( $user_id ) {
	$etat = get_user_meta( absint( $user_id ), 'axz_paiement', true );
	return in_array( $etat, array( 'a_jour', 'en_attente' ), true ) ? $etat : 'en_attente';
}

function axz_page_paiements() {
	if ( ! current_user_can( 'axz_manage_subscriptions' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}

	if ( isset( $_POST['axz_paiement_submit'] ) ) {
		/* Nonce vérifié à la main : un échec est journalisé (nonce_invalide). */
		axz_verify_nonce_log( 'axz_paiement', 'axz_paiement_nonce' );
		if ( ! current_user_can( 'axz_manage_subscriptions' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
		}
		$user_id = isset( $_POST['axz_user_id'] ) ? absint( $_POST['axz_user_id'] ) : 0;
		$user    = get_user_by( 'id', $user_id );
		if ( ! $user || ! in_array( 'client_aelyra', (array) $user->roles, true ) ) {
			wp_die( esc_html__( 'Client introuvable.', 'axz' ) );
		}
		$etat = isset( $_POST['axz_etat'] ) ? sanitize_key( wp_unslash( $_POST['axz_etat'] ) ) : '';
		if ( ! in_array( $etat, array( 'a_jour', 'en_attente' ), true ) ) {
			wp_die( esc_html__( 'Valeur invalide.', 'axz' ) );
		}
		update_user_meta( $user_id, 'axz_paiement', $etat );
		axz_log( $user_id, 'paiement_' . $etat, sprintf( __( 'Paiement marqué « %s » (validation manuelle).', 'axz' ), $etat ) );
		wp_safe_redirect( add_query_arg(
			array( 'page' => 'axz-paiements', 'axz_msg' => 'ok' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Statut de paiement mis à jour.', 'axz' ) . '</p></div>';
	}

	$offres  = axz_offres();
	$query   = axz_get_clients( array( 'number' => 500, 'fields' => 'all' ) );
	$clients = $query->get_results();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Paiements', 'axz' ); ?></h1>
		<div class="axz-note">
			<?php esc_html_e( 'Pas de prélèvement automatique Mobile Money pour le moment : la validation se fait manuellement ici, mois par mois, en attendant le branchement de l\u2019agrégateur de paiement.', 'axz' ); ?>
		</div>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Client', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Offre', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Statut paiement', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Action', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $clients as $user ) : ?>
					<?php
					$offre = axz_client_offre( $user->ID );
					$etat  = axz_paiement_statut( $user->ID );
					?>
					<tr>
						<td><strong><?php echo esc_html( $user->display_name ); ?></strong><br><span style="color:#777"><?php echo esc_html( $user->user_email ); ?></span></td>
						<td><?php echo esc_html( $offres[ $offre ]['label'] . ' — ' . axz_prix_formate( $offres[ $offre ]['prix'] ) ); ?></td>
						<td>
							<?php if ( 'a_jour' === $etat ) : ?>
								<span class="axz-badge actif"><?php esc_html_e( 'Payé', 'axz' ); ?></span>
							<?php else : ?>
								<span class="axz-badge en_attente"><?php esc_html_e( 'En attente', 'axz' ); ?></span>
							<?php endif; ?>
						</td>
						<td>
							<form method="post" class="axz-rowform">
								<?php wp_nonce_field( 'axz_paiement', 'axz_paiement_nonce' ); ?>
								<input type="hidden" name="axz_user_id" value="<?php echo (int) $user->ID; ?>">
								<?php if ( 'a_jour' === $etat ) : ?>
									<input type="hidden" name="axz_etat" value="en_attente">
									<button type="submit" name="axz_paiement_submit" class="button"><?php esc_html_e( 'Marquer en attente', 'axz' ); ?></button>
								<?php else : ?>
									<input type="hidden" name="axz_etat" value="a_jour">
									<button type="submit" name="axz_paiement_submit" class="button button-primary"><?php esc_html_e( 'Marquer payé', 'axz' ); ?></button>
								<?php endif; ?>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   3. Plafonds de coût — widget d'alerte sur la vue d'ensemble
------------------------------------------------------------------ */
function axz_render_alerte_plafonds() {
	/* La capacité est déjà vérifiée par la page appelante (vue d'ensemble). */
	$plafond_w = max( 0, (float) axz_get_option( 'plafond_whatsapp', 10 ) );
	$plafond_i = max( 0, (float) axz_get_option( 'plafond_ia', 5 ) );

	$query        = axz_get_clients( array( 'number' => 500, 'fields' => array( 'ID', 'display_name' ) ) );
	$depassements = array();
	$has_data     = false;

	foreach ( $query->get_results() as $c ) {
		$uw = get_user_meta( $c->ID, 'axz_usage_whatsapp', true );
		$ui = get_user_meta( $c->ID, 'axz_usage_ia', true );
		if ( '' !== $uw || '' !== $ui ) {
			$has_data = true;
		}
		$msgs = array();
		if ( '' !== $uw && (float) $uw > $plafond_w ) {
			/* translators: 1 = usage, 2 = plafond */
			$msgs[] = sprintf( esc_html__( 'WhatsApp : %1$s $ / plafond %2$s $', 'axz' ), esc_html( (string) $uw ), esc_html( (string) $plafond_w ) );
		}
		if ( '' !== $ui && (float) $ui > $plafond_i ) {
			$msgs[] = sprintf( esc_html__( 'IA : %1$s $ / plafond %2$s $', 'axz' ), esc_html( (string) $ui ), esc_html( (string) $plafond_i ) );
		}
		if ( $msgs ) {
			$depassements[] = array( 'nom' => $c->display_name, 'msgs' => $msgs );
		}
	}
	?>
	<div class="axz-alert-box">
		<div class="s-tag"><?php esc_html_e( 'PLAFONDS DE COÛT', 'axz' ); ?></div>
		<?php if ( ! $has_data ) : ?>
			<p class="s-sub"><?php esc_html_e( 'Consommation : en attente du connecteur agent — aucune donnée d\u2019usage reçue pour le moment.', 'axz' ); ?></p>
		<?php elseif ( empty( $depassements ) ) : ?>
			<p class="s-sub"><?php esc_html_e( 'Aucun dépassement de plafond ce mois-ci.', 'axz' ); ?></p>
		<?php else : ?>
			<ul>
				<?php foreach ( $depassements as $d ) : ?>
					<li><strong><?php echo esc_html( $d['nom'] ); ?></strong> — <?php echo implode( ' · ', $d['msgs'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- déjà échappé ci-dessus ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   5. Alertes Oxyne Z — structure prête, état vide honnête
------------------------------------------------------------------ */
function axz_oxyne_alertes() {
	$a = get_option( 'axz_oxyne_alertes', array() );
	return is_array( $a ) ? $a : array();
}

/**
 * Ajoute une alerte (réservé au futur connecteur de veille).
 * Statuts : en_attente, validee, rejetee.
 */
function axz_oxyne_add_alerte( $type, $cible, $preuve = '' ) {
	$alertes = axz_oxyne_alertes();
	$id      = 'axz_' . gmdate( 'YmdHis' ) . '_' . wp_rand( 1000, 9999 );
	$alertes[] = array(
		'id'        => $id,
		'date'      => current_time( 'mysql' ),
		'type'      => sanitize_text_field( $type ),
		'cible'     => sanitize_text_field( $cible ),
		'preuve'    => is_string( $preuve ) ? $preuve : '',
		'statut'    => 'en_attente',
		'decide_par'=> 0,
		'decide_le' => '',
	);
	update_option( 'axz_oxyne_alertes', $alertes );
	return $id;
}

function axz_oxyne_statut_label( $statut ) {
	$map = array(
		'en_attente' => __( 'En attente', 'axz' ),
		'validee'    => __( 'Validée', 'axz' ),
		'rejetee'    => __( 'Rejetée', 'axz' ),
	);
	return isset( $map[ $statut ] ) ? $map[ $statut ] : $statut;
}

function axz_page_alertes_oxyne() {
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}

	/* Traitement : Valider / Rejeter une alerte. */
	if ( isset( $_GET['axz_oxyne_action'] ) ) {
		/* Nonce vérifié à la main : un échec est journalisé (nonce_invalide). */
		axz_verify_nonce_log( 'axz_oxyne_alerte' );
		if ( ! current_user_can( 'axz_manage_clients' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
		}
		$action = sanitize_key( wp_unslash( $_GET['axz_oxyne_action'] ) );
		if ( ! in_array( $action, array( 'valider', 'rejeter' ), true ) ) {
			wp_die( esc_html__( 'Action inconnue.', 'axz' ) );
		}
		$alerte_id = isset( $_GET['alerte_id'] ) ? sanitize_key( wp_unslash( $_GET['alerte_id'] ) ) : '';
		$alertes   = axz_oxyne_alertes();
		$found     = null;
		foreach ( $alertes as $idx => $a ) {
			if ( isset( $a['id'] ) && $a['id'] === $alerte_id && 'en_attente' === $a['statut'] ) {
				$found = $idx;
				break;
			}
		}
		if ( null === $found ) {
			wp_die( esc_html__( 'Alerte introuvable ou déjà traitée.', 'axz' ) );
		}
		$alertes[ $found ]['statut']     = ( 'valider' === $action ) ? 'validee' : 'rejetee';
		$alertes[ $found ]['decide_par'] = get_current_user_id();
		$alertes[ $found ]['decide_le']  = current_time( 'mysql' );
		update_option( 'axz_oxyne_alertes', $alertes );
		axz_log(
			0,
			( 'valider' === $action ) ? 'oxyne_alerte_validee' : 'oxyne_alerte_rejetee',
			sprintf( __( 'Alerte %s (%s — %s).', 'axz' ), $alerte_id, $alertes[ $found ]['type'], $alertes[ $found ]['cible'] )
		);
		wp_safe_redirect( add_query_arg(
			array( 'page' => 'axz-alertes-oxyne', 'axz_msg' => 'ok' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Décision enregistrée.', 'axz' ) . '</p></div>';
	}

	$alertes = array_reverse( axz_oxyne_alertes() );
	$base    = admin_url( 'admin.php?page=axz-alertes-oxyne' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Alertes Oxyne Z', 'axz' ); ?></h1>
		<p><?php esc_html_e( 'Oxyne Z détecte, prouve et fait valider : rien n\u2019est signalé aux plateformes sans ton accord.', 'axz' ); ?></p>
		<?php if ( empty( $alertes ) ) : ?>
			<div class="axz-note"><?php esc_html_e( 'Module de veille non encore branché — les alertes apparaîtront ici.', 'axz' ); ?></div>
		<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Type', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Cible', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Preuve', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Décision', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $alertes as $a ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $a['date'] ) ) ); ?></td>
						<td><strong><?php echo esc_html( $a['type'] ); ?></strong></td>
						<td><?php echo esc_html( $a['cible'] ); ?></td>
						<td><?php echo $a['preuve'] ? esc_html( $a['preuve'] ) : '<span style="color:#999">—</span>'; ?></td>
						<td><span class="axz-badge <?php echo esc_attr( $a['statut'] ); ?>"><?php echo esc_html( axz_oxyne_statut_label( $a['statut'] ) ); ?></span></td>
						<td>
							<?php if ( 'en_attente' === $a['statut'] ) : ?>
								<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'axz_oxyne_action' => 'valider', 'alerte_id' => $a['id'] ), $base ), 'axz_oxyne_alerte' ) ); ?>"><?php esc_html_e( 'Valider', 'axz' ); ?></a>
								<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'axz_oxyne_action' => 'rejeter', 'alerte_id' => $a['id'] ), $base ), 'axz_oxyne_alerte' ) ); ?>"><?php esc_html_e( 'Rejeter', 'axz' ); ?></a>
							<?php else : ?>
								<span style="color:#777"><?php esc_html_e( 'Traitée', 'axz' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   4 (suite). Journal d'activité — page paginée + filtre par client
------------------------------------------------------------------ */
function axz_page_journal() {
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	global $wpdb;
	$table  = axz_journal_table();
	$per_page = 20;
	$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
	$offset   = ( $paged - 1 ) * $per_page;

	$client_filter = isset( $_GET['client_id'] ) ? absint( $_GET['client_id'] ) : 0;

	if ( $client_filter > 0 ) {
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE client_id = %d", $client_filter ) );
		$rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table WHERE client_id = %d ORDER BY date DESC LIMIT %d OFFSET %d",
			$client_filter, $per_page, $offset
		) );
	} else {
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		$rows  = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table ORDER BY date DESC LIMIT %d OFFSET %d",
			$per_page, $offset
		) );
	}

	$clients = axz_get_clients( array( 'number' => 500, 'fields' => array( 'ID', 'display_name' ) ) )->get_results();
	$pages   = max( 1, (int) ceil( $total / $per_page ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Journal d\u2019activité', 'axz' ); ?></h1>
		<form method="get" class="axz-rowform" style="margin-bottom:16px">
			<input type="hidden" name="page" value="axz-journal">
			<label for="axz-client-filter"><?php esc_html_e( 'Filtrer par client :', 'axz' ); ?></label>
			<select name="client_id" id="axz-client-filter">
				<option value="0"><?php esc_html_e( 'Tous', 'axz' ); ?></option>
				<?php foreach ( $clients as $c ) : ?>
					<option value="<?php echo (int) $c->ID; ?>" <?php selected( $client_filter, (int) $c->ID ); ?>>
						<?php echo esc_html( $c->display_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'axz' ); ?></button>
		</form>
		<?php if ( empty( $rows ) ) : ?>
			<div class="axz-note"><?php esc_html_e( 'Aucune activité enregistrée pour le moment.', 'axz' ); ?></div>
		<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Auteur', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Client', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Action', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Détails', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<?php
					$auteur = $r->auteur_id ? get_user_by( 'id', (int) $r->auteur_id ) : null;
					$client = $r->client_id ? get_user_by( 'id', (int) $r->client_id ) : null;
					?>
					<tr>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $r->date ) ) ); ?></td>
						<td><?php echo $auteur ? esc_html( $auteur->display_name ) : '<span style="color:#999">—</span>'; ?></td>
						<td><?php echo $client ? esc_html( $client->display_name ) : '<span style="color:#999">—</span>'; ?></td>
						<td><?php echo esc_html( axz_action_label( $r->action ) ); ?></td>
						<td><?php echo esc_html( $r->details ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php
				echo paginate_links( array(
					'base'    => add_query_arg( array( 'paged' => '%#%', 'client_id' => $client_filter ) ),
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

/* ------------------------------------------------------------------
   6. Réglages — Settings API
------------------------------------------------------------------ */
function axz_register_settings() {
	register_setting( 'axz_settings', 'axz_options', array(
		'sanitize_callback' => 'axz_sanitize_options',
	) );

	add_settings_section(
		'axz_sec_api',
		__( 'Clés API', 'axz' ),
		'axz_sec_api_cb',
		'axz-reglages'
	);
	foreach ( array(
		'whatsapp_token'   => __( 'Jeton WhatsApp Business', 'axz' ),
		'whatsapp_phone_id'=> __( 'Identifiant du numéro WhatsApp', 'axz' ),
		'openai_api_key'   => __( 'Clé API du modèle d\u2019IA', 'axz' ),
		'tiktok_client_key'=> __( 'Clé client TikTok', 'axz' ),
	) as $key => $label ) {
		add_settings_field(
			$key,
			$label,
			'axz_field_password',
			'axz-reglages',
			'axz_sec_api',
			array( 'key' => $key )
		);
	}

	add_settings_section(
		'axz_sec_plafonds',
		__( 'Plafonds de coût', 'axz' ),
		'axz_sec_plafonds_cb',
		'axz-reglages'
	);
	add_settings_field( 'plafond_whatsapp', __( 'Plafond WhatsApp ($/mois/client)', 'axz' ), 'axz_field_number', 'axz-reglages', 'axz_sec_plafonds', array( 'key' => 'plafond_whatsapp', 'default' => 10 ) );
	add_settings_field( 'plafond_ia', __( 'Plafond IA ($/mois/client)', 'axz' ), 'axz_field_number', 'axz-reglages', 'axz_sec_plafonds', array( 'key' => 'plafond_ia', 'default' => 5 ) );

	add_settings_section(
		'axz_sec_general',
		__( 'Général', 'axz' ),
		'axz_sec_general_cb',
		'axz-reglages'
	);
	add_settings_field( 'duree_essai', __( 'Durée d\u2019essai (jours)', 'axz' ), 'axz_field_number_int', 'axz-reglages', 'axz_sec_general', array( 'key' => 'duree_essai', 'default' => 7 ) );
	add_settings_field( 'devise', __( 'Devise', 'axz' ), 'axz_field_text', 'axz-reglages', 'axz_sec_general', array( 'key' => 'devise', 'default' => 'F CFA' ) );
}
add_action( 'admin_init', 'axz_register_settings' );

function axz_sec_api_cb() {
	echo '<p>' . esc_html__( 'À remplir quand le financement arrive. Les clés sont stockées dans la base et ne sont jamais affichées en clair.', 'axz' ) . '</p>';
}
function axz_sec_plafonds_cb() {
	echo '<p>' . esc_html__( 'Alerte sur la vue d\u2019ensemble quand un client dépasse son plafond mensuel.', 'axz' ) . '</p>';
}
function axz_sec_general_cb() {
	echo '<p>' . esc_html__( 'Paramètres généraux du service.', 'axz' ) . '</p>';
}

function axz_field_password( $args ) {
	$key   = $args['key'];
	$val   = axz_get_option( $key, '' );
	$saved = '' !== $val;
	?>
	<input type="password" name="axz_options[<?php echo esc_attr( $key ); ?>]" value="" autocomplete="new-password" class="regular-text" aria-label="<?php echo esc_attr( $key ); ?>">
	<?php if ( $saved ) : ?>
		<span class="description"><?php esc_html_e( '●●●●●●●● enregistrée — laisse vide pour conserver.', 'axz' ); ?></span>
	<?php else : ?>
		<span class="description"><?php esc_html_e( 'Non renseignée.', 'axz' ); ?></span>
	<?php endif; ?>
	<?php
}

function axz_field_number( $args ) {
	$key = $args['key'];
	$val = axz_get_option( $key, $args['default'] );
	?>
	<input type="number" step="0.01" min="0" name="axz_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $val ); ?>" class="small-text">
	<?php
}

function axz_field_number_int( $args ) {
	$key = $args['key'];
	$val = absint( axz_get_option( $key, $args['default'] ) );
	?>
	<input type="number" step="1" min="1" name="axz_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo (int) $val; ?>" class="small-text">
	<?php
}

function axz_field_text( $args ) {
	$key = $args['key'];
	$val = axz_get_option( $key, $args['default'] );
	?>
	<input type="text" name="axz_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( (string) $val ); ?>" class="regular-text">
	<?php
}

/* Assainissement : les clés vides conservent l'ancienne valeur. */
function axz_sanitize_options( $input ) {
	$old = get_option( 'axz_options', array() );
	$out = is_array( $old ) ? $old : array();

	foreach ( array( 'whatsapp_token', 'whatsapp_phone_id', 'openai_api_key', 'tiktok_client_key' ) as $k ) {
		$v = isset( $input[ $k ] ) ? trim( (string) $input[ $k ] ) : '';
		if ( '' !== $v ) {
			$out[ $k ] = sanitize_text_field( $v );
		}
	}

	$out['plafond_whatsapp'] = isset( $input['plafond_whatsapp'] ) ? max( 0, (float) $input['plafond_whatsapp'] ) : 10;
	$out['plafond_ia']       = isset( $input['plafond_ia'] ) ? max( 0, (float) $input['plafond_ia'] ) : 5;

	$duree = isset( $input['duree_essai'] ) ? absint( $input['duree_essai'] ) : 7;
	$out['duree_essai'] = $duree > 0 ? $duree : 7;

	$out['devise'] = isset( $input['devise'] ) && '' !== trim( (string) $input['devise'] )
		? sanitize_text_field( $input['devise'] )
		: 'F CFA';

	return $out;
}

function axz_page_reglages() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Réglages', 'axz' ); ?></h1>
		<div class="axz-note">
			<?php esc_html_e( 'À remplir quand le financement arrive — le site fonctionne sans, en mode vitrine + gestion manuelle.', 'axz' ); ?>
		</div>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'axz_settings' );
			do_settings_sections( 'axz-reglages' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
