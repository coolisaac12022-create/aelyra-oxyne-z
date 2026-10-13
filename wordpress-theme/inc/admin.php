<?php
/**
 * Aelyra Oxyne Z — tableau de bord administrateur.
 *
 * Menu de premier niveau « Aelyra » (capacité axz_view_dashboard) :
 *  - Vue d'ensemble : clients, essais en cours, répartition par offre, MRR estimé.
 *  - Clients (axz_manage_clients) : WP_List_Table avec recherche, pagination,
 *    actions Valider / Suspendre / Réactiver (nonces + vérification du rôle).
 *  - Abonnements (axz_manage_subscriptions) : changement d'offre et de statut
 *    via formulaire sécurisé (nonce + liste blanche + vérification du rôle).
 *
 * Anti Broken Access Control : chaque page vérifie sa capacité, chaque action
 * vérifie capacité + nonce + que la cible est bien un client_aelyra.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
   Menu
------------------------------------------------------------------ */
function axz_admin_menu() {
	add_menu_page(
		__( 'Aelyra', 'axz' ),
		__( 'Aelyra', 'axz' ),
		'axz_view_dashboard',
		'axz-dashboard',
		'axz_page_dashboard',
		'dashicons-groups',
		30
	);
	add_submenu_page(
		'axz-dashboard',
		__( "Vue d'ensemble", 'axz' ),
		__( "Vue d'ensemble", 'axz' ),
		'axz_view_dashboard',
		'axz-dashboard',
		'axz_page_dashboard'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Clients', 'axz' ),
		__( 'Clients', 'axz' ),
		'axz_manage_clients',
		'axz-clients',
		'axz_page_clients'
	);
	add_submenu_page(
		'axz-dashboard',
		__( 'Abonnements', 'axz' ),
		__( 'Abonnements', 'axz' ),
		'axz_manage_subscriptions',
		'axz-abonnements',
		'axz_page_abonnements'
	);
}
add_action( 'admin_menu', 'axz_admin_menu' );

/* ------------------------------------------------------------------
   Données : lecture centralisée des clients
------------------------------------------------------------------ */
function axz_get_clients( $args = array() ) {
	$defaults = array(
		'role'    => 'client_aelyra',
		'orderby' => 'registered',
		'order'   => 'DESC',
	);
	$query = new WP_User_Query( wp_parse_args( $args, $defaults ) );
	return $query;
}

function axz_client_statut( $user_id ) {
	$statuts = axz_statuts();
	$statut  = get_user_meta( $user_id, 'axz_statut', true );
	return isset( $statuts[ $statut ] ) ? $statut : 'essai';
}

function axz_client_offre( $user_id ) {
	$offres = axz_offres();
	$offre  = get_user_meta( $user_id, 'axz_offre', true );
	return isset( $offres[ $offre ] ) ? $offre : 'essentiel';
}

/* ------------------------------------------------------------------
   1. Vue d'ensemble
------------------------------------------------------------------ */
function axz_page_dashboard() {
	if ( ! current_user_can( 'axz_view_dashboard' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}

	$query   = axz_get_clients( array( 'fields' => array( 'ID' ) ) );
	$clients = $query->get_results();
	$offres  = axz_offres();

	$total   = count( $clients );
	$essais  = 0;
	$par_offre = array( 'essentiel' => 0, 'pro' => 0, 'premium' => 0 );
	$mrr     = 0;

	foreach ( $clients as $c ) {
		$offre  = axz_client_offre( $c->ID );
		$statut = axz_client_statut( $c->ID );
		$par_offre[ $offre ]++;
		if ( 'essai' === $statut ) {
			$essais++;
		}
		/* MRR : seuls les abonnements ACTIFS rapportent. */
		if ( 'actif' === $statut ) {
			$mrr += $offres[ $offre ]['prix'];
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Vue d\u2019ensemble', 'axz' ); ?></h1>
		<div class="axz-stats">
			<div class="axz-stat">
				<div class="s-tag"><?php esc_html_e( 'CLIENTS', 'axz' ); ?></div>
				<div class="s-val"><?php echo (int) $total; ?></div>
				<div class="s-sub"><?php esc_html_e( 'comptes clients au total', 'axz' ); ?></div>
			</div>
			<div class="axz-stat">
				<div class="s-tag"><?php esc_html_e( 'ESSAIS EN COURS', 'axz' ); ?></div>
				<div class="s-val"><?php echo (int) $essais; ?></div>
				<div class="s-sub"><?php esc_html_e( '7 jours gratuits, à valider', 'axz' ); ?></div>
			</div>
			<div class="axz-stat">
				<div class="s-tag"><?php esc_html_e( 'RÉPARTITION', 'axz' ); ?></div>
				<div class="s-val" style="font-size:22px">
					<?php echo (int) $par_offre['essentiel']; ?> / <?php echo (int) $par_offre['pro']; ?> / <?php echo (int) $par_offre['premium']; ?>
				</div>
				<div class="s-sub"><?php esc_html_e( 'Essentiel / Pro / Premium', 'axz' ); ?></div>
			</div>
			<div class="axz-stat">
				<div class="s-tag"><?php esc_html_e( 'MRR ESTIMÉ', 'axz' ); ?></div>
				<div class="s-val" style="font-size:26px"><?php echo esc_html( axz_prix_formate( $mrr ) ); ?></div>
				<div class="s-sub"><?php esc_html_e( 'abonnements actifs / mois', 'axz' ); ?></div>
			</div>
		</div>
		<div class="axz-note">
			<?php esc_html_e( 'Le MRR compte uniquement les abonnements au statut « Actif ». Les essais gratuits ne sont pas comptés tant qu\u2019ils ne sont pas validés dans l\u2019onglet Clients.', 'axz' ); ?>
		</div>
		<?php
		/* Widget plafonds de coût (extensions) : alertes de dépassement. */
		if ( function_exists( 'axz_render_alerte_plafonds' ) ) {
			axz_render_alerte_plafonds();
		}
		?>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   2. Clients — WP_List_Table
------------------------------------------------------------------ */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AXZ_Clients_Table extends WP_List_Table {

	public function __construct() {
		parent::__construct( array(
			'singular' => 'client',
			'plural'   => 'clients',
			'ajax'     => false,
		) );
	}

	public function get_columns() {
		return array(
			'nom'       => __( 'Nom', 'axz' ),
			'email'     => __( 'E-mail', 'axz' ),
			'telephone' => __( 'Téléphone', 'axz' ),
			'offre'     => __( 'Offre', 'axz' ),
			'statut'    => __( 'Statut', 'axz' ),
			'fin_essai' => __( "Fin d'essai", 'axz' ),
			'actions'   => __( 'Actions', 'axz' ),
		);
	}

	public function prepare_items() {
		$per_page = 20;
		$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		$args = array(
			'role'    => 'client_aelyra',
			'number'  => $per_page,
			'offset'  => ( $paged - 1 ) * $per_page,
			'orderby' => 'registered',
			'order'   => 'DESC',
		);
		if ( '' !== $search ) {
			$args['search'] = '*' . $search . '*';
		}

		$query = axz_get_clients( $args );
		$this->items = $query->get_results();

		$this->set_pagination_args( array(
			'total_items' => $query->get_total(),
			'per_page'    => $per_page,
		) );
		$this->_column_headers = array( $this->get_columns(), array(), array() );
	}

	public function column_default( $user, $column_name ) {
		switch ( $column_name ) {
			case 'nom':
				return '<strong>' . esc_html( $user->display_name ) . '</strong><br><span style="color:#777">' . esc_html( $user->user_login ) . '</span>';
			case 'email':
				return esc_html( $user->user_email );
			case 'telephone':
				$tel = get_user_meta( $user->ID, 'axz_telephone', true );
				return $tel ? esc_html( $tel ) : '<span style="color:#999">—</span>';
			case 'offre':
				$offres = axz_offres();
				$offre  = axz_client_offre( $user->ID );
				return esc_html( $offres[ $offre ]['label'] ) . ' <span style="color:#777">(' . esc_html( axz_prix_formate( $offres[ $offre ]['prix'] ) ) . ')</span>';
			case 'statut':
				$statuts = axz_statuts();
				$statut  = axz_client_statut( $user->ID );
				return '<span class="axz-badge ' . esc_attr( $statut ) . '">' . esc_html( $statuts[ $statut ] ) . '</span>';
			case 'fin_essai':
				$fin = get_user_meta( $user->ID, 'axz_fin_essai', true );
				if ( ! $fin ) {
					return '<span style="color:#999">—</span>';
				}
				return esc_html( date_i18n( get_option( 'date_format' ), strtotime( $fin ) ) );
			case 'actions':
				return $this->actions_links( $user );
			default:
				return '';
		}
	}

	private function actions_links( $user ) {
		$statut = axz_client_statut( $user->ID );
		$links  = array();
		$base   = admin_url( 'admin.php?page=axz-clients' );

		if ( 'essai' === $statut || 'suspendu' === $statut ) {
			$links[] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'axz_action' => 'valider', 'user_id' => $user->ID ), $base ), 'axz_client_action' ) ) . '">' . esc_html__( 'Valider', 'axz' ) . '</a>';
		}
		if ( 'actif' === $statut || 'essai' === $statut ) {
			$links[] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'axz_action' => 'suspendre', 'user_id' => $user->ID ), $base ), 'axz_client_action' ) ) . '" style="color:#b32d2e">' . esc_html__( 'Suspendre', 'axz' ) . '</a>';
		}
		if ( 'suspendu' === $statut ) {
			$links[] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'axz_action' => 'reactiver', 'user_id' => $user->ID ), $base ), 'axz_client_action' ) ) . '">' . esc_html__( 'Réactiver', 'axz' ) . '</a>';
		}
		return implode( ' | ', $links );
	}
}

/* Traitement des actions Valider / Suspendre / Réactiver.
   Triple garde : capacité + nonce + la cible est bien un client_aelyra. */
function axz_handle_client_actions() {
	if ( ! isset( $_GET['page'] ) || 'axz-clients' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
		return;
	}
	if ( ! isset( $_GET['axz_action'] ) ) {
		return;
	}
	check_admin_referer( 'axz_client_action' );
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	$action = sanitize_key( wp_unslash( $_GET['axz_action'] ) );
	$map    = array(
		'valider'   => 'actif',
		'suspendre' => 'suspendu',
		'reactiver' => 'actif',
	);
	if ( ! isset( $map[ $action ] ) ) {
		wp_die( esc_html__( 'Action inconnue.', 'axz' ) );
	}
	$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;
	$user    = get_user_by( 'id', $user_id );
	/* Anti Broken Access Control : on refuse d'agir sur un non-client
	   (ni admin, ni abonné, ni compte inexistant). */
	if ( ! $user || ! in_array( 'client_aelyra', (array) $user->roles, true ) ) {
		wp_die( esc_html__( 'Client introuvable.', 'axz' ) );
	}
	update_user_meta( $user_id, 'axz_statut', $map[ $action ] );
	if ( function_exists( 'axz_log' ) ) {
		$statuts = axz_statuts();
		axz_log( $user_id, 'client_' . $action, sprintf( __( 'Statut passé à « %s ».', 'axz' ), $statuts[ $map[ $action ] ] ) );
	}
	wp_safe_redirect( add_query_arg(
		array( 'page' => 'axz-clients', 'axz_msg' => 'ok' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_init', 'axz_handle_client_actions' );

function axz_page_clients() {
	if ( ! current_user_can( 'axz_manage_clients' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Statut mis à jour.', 'axz' ) . '</p></div>';
	}
	$table = new AXZ_Clients_Table();
	$table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Clients', 'axz' ); ?></h1>
		<form method="get">
			<input type="hidden" name="page" value="axz-clients">
			<?php $table->search_box( __( 'Rechercher un client', 'axz' ), 'axz-client' ); ?>
		</form>
		<form method="get">
			<input type="hidden" name="page" value="axz-clients">
			<?php $table->display(); ?>
		</form>
	</div>
	<?php
}

/* ------------------------------------------------------------------
   3. Abonnements — changement d'offre / statut par formulaire sécurisé
------------------------------------------------------------------ */
function axz_handle_abonnement_update() {
	if ( ! isset( $_POST['axz_abonnement_submit'] ) ) {
		return;
	}
	check_admin_referer( 'axz_abonnement', 'axz_abonnement_nonce' );
	if ( ! current_user_can( 'axz_manage_subscriptions' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	$user_id = isset( $_POST['axz_user_id'] ) ? absint( $_POST['axz_user_id'] ) : 0;
	$user    = get_user_by( 'id', $user_id );
	if ( ! $user || ! in_array( 'client_aelyra', (array) $user->roles, true ) ) {
		wp_die( esc_html__( 'Client introuvable.', 'axz' ) );
	}
	$offre  = isset( $_POST['axz_offre'] ) ? sanitize_key( wp_unslash( $_POST['axz_offre'] ) ) : '';
	$statut = isset( $_POST['axz_statut'] ) ? sanitize_key( wp_unslash( $_POST['axz_statut'] ) ) : '';
	/* Listes blanches strictes : rien d'autre ne passe. */
	if ( ! array_key_exists( $offre, axz_offres() ) || ! array_key_exists( $statut, axz_statuts() ) ) {
		wp_die( esc_html__( 'Valeur invalide.', 'axz' ) );
	}
	update_user_meta( $user_id, 'axz_offre', $offre );
	update_user_meta( $user_id, 'axz_statut', $statut );
	if ( function_exists( 'axz_log' ) ) {
		$offres  = axz_offres();
		$statuts = axz_statuts();
		axz_log( $user_id, 'abonnement_modifie', sprintf( __( 'Offre : %s — Statut : %s.', 'axz' ), $offres[ $offre ]['label'], $statuts[ $statut ] ) );
	}
	wp_safe_redirect( add_query_arg(
		array( 'page' => 'axz-abonnements', 'axz_msg' => 'ok' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_init', 'axz_handle_abonnement_update' );

function axz_page_abonnements() {
	if ( ! current_user_can( 'axz_manage_subscriptions' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'axz' ) );
	}
	if ( isset( $_GET['axz_msg'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['axz_msg'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Abonnement mis à jour.', 'axz' ) . '</p></div>';
	}
	$offres  = axz_offres();
	$statuts = axz_statuts();
	$query   = axz_get_clients( array( 'number' => 100, 'fields' => 'all' ) );
	$clients = $query->get_results();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Aelyra — Abonnements', 'axz' ); ?></h1>
		<p><?php esc_html_e( 'Change l\u2019offre ou le statut d\u2019un client. Chaque ligne est un formulaire indépendant et sécurisé.', 'axz' ); ?></p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Client', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Offre', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'axz' ); ?></th>
					<th><?php esc_html_e( 'Mettre à jour', 'axz' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $clients as $user ) : ?>
					<?php
					$offre_c  = axz_client_offre( $user->ID );
					$statut_c = axz_client_statut( $user->ID );
					?>
					<tr>
						<td><strong><?php echo esc_html( $user->display_name ); ?></strong><br><span style="color:#777"><?php echo esc_html( $user->user_email ); ?></span></td>
						<td>
							<form method="post" class="axz-rowform">
								<?php wp_nonce_field( 'axz_abonnement', 'axz_abonnement_nonce' ); ?>
								<input type="hidden" name="axz_user_id" value="<?php echo (int) $user->ID; ?>">
								<select name="axz_offre" aria-label="<?php esc_attr_e( 'Offre', 'axz' ); ?>">
									<?php foreach ( $offres as $key => $o ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $offre_c, $key ); ?>>
											<?php echo esc_html( $o['label'] . ' — ' . axz_prix_formate( $o['prix'] ) ); ?>
										</option>
									<?php endforeach; ?>
								</select>
						</td>
						<td>
								<select name="axz_statut" aria-label="<?php esc_attr_e( 'Statut', 'axz' ); ?>">
									<?php foreach ( $statuts as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $statut_c, $key ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
						</td>
						<td>
								<button type="submit" name="axz_abonnement_submit" class="button button-primary"><?php esc_html_e( 'Mettre à jour', 'axz' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="axz-note">
			<?php esc_html_e( 'Rappel : seul un administrateur peut changer une offre ou un statut. Les clients ne voient que leurs propres données dans leur espace client.', 'axz' ); ?>
		</div>
	</div>
	<?php
}
