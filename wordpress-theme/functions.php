<?php
/**
 * Aelyra Oxyne Z — functions.php
 * Vitrine + inscription + espace client + tableau de bord admin.
 */
defined( 'ABSPATH' ) || exit;

define( 'AXZ_VERSION', '1.0.0' );

require_once get_template_directory() . '/inc/roles.php';
require_once get_template_directory() . '/inc/security.php';
require_once get_template_directory() . '/inc/admin.php';
require_once get_template_directory() . '/inc/admin-extensions.php';

/* ------------------------------------------------------------------
   Réglages du thème
------------------------------------------------------------------ */
function axz_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array(
		'principal' => __( 'Menu principal', 'axz' ),
		'pied'      => __( 'Menu pied de page', 'axz' ),
	) );
}
add_action( 'after_setup_theme', 'axz_setup' );

/* ------------------------------------------------------------------
   Chargements (front)
------------------------------------------------------------------ */
function axz_enqueue() {
	wp_enqueue_style(
		'axz-fonts',
		'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,640;1,9..144,480&family=Archivo:wght@400;500;600;700&display=swap',
		array(),
		AXZ_VERSION
	);
	wp_enqueue_style( 'axz-charte', get_template_directory_uri() . '/assets/css/charte.css', array( 'axz-fonts' ), AXZ_VERSION );
	wp_enqueue_style( 'axz-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'axz-charte' ), AXZ_VERSION );
	wp_enqueue_script( 'axz-site', get_template_directory_uri() . '/assets/js/site.js', array(), AXZ_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'axz_enqueue' );

/* ------------------------------------------------------------------
   Chargements (admin) — uniquement sur les pages du menu Aelyra
------------------------------------------------------------------ */
function axz_admin_enqueue( $hook ) {
	if ( false === strpos( (string) $hook, 'axz-' ) ) {
		return;
	}
	wp_enqueue_style( 'axz-admin', get_template_directory_uri() . '/assets/css/admin.css', array(), AXZ_VERSION );
}
add_action( 'admin_enqueue_scripts', 'axz_admin_enqueue' );

/* ------------------------------------------------------------------
   Données métier : les offres décidées par Isaac
------------------------------------------------------------------ */
function axz_offres() {
	return array(
		'essentiel' => array(
			'label' => __( 'Essentiel', 'axz' ),
			'prix'  => 6000,
			'desc'  => __( 'Réception WhatsApp : tri par priorité, rendez-vous, catalogue, liens Mobile Money.', 'axz' ),
		),
		'pro'       => array(
			'label' => __( 'Pro', 'axz' ),
			'prix'  => 10000,
			'desc'  => __( 'Essentiel + Messenger/Instagram + publication auto des produits.', 'axz' ),
		),
		'premium'   => array(
			'label' => __( 'Premium', 'axz' ),
			'prix'  => 60000,
			'desc'  => __( 'Aelyra complète avec appels + Oxyne Z complet.', 'axz' ),
		),
	);
}

function axz_statuts() {
	return array(
		'essai'    => __( 'Essai', 'axz' ),
		'actif'    => __( 'Actif', 'axz' ),
		'suspendu' => __( 'Suspendu', 'axz' ),
	);
}

function axz_prix_formate( $montant ) {
	return number_format( (int) $montant, 0, ',', ' ' ) . ' F';
}

/* ------------------------------------------------------------------
   Tableau de bord du client (utilisé par le modèle ET le shortcode).
   Règle stricte : on ne lit que les données de l'utilisateur connecté.
------------------------------------------------------------------ */
function axz_render_dashboard( $user_id ) {
	$user_id = absint( $user_id );
	if ( 0 === $user_id || $user_id !== get_current_user_id() ) {
		return '<p>' . esc_html__( 'Connecte-toi pour voir ton espace client.', 'axz' ) . '</p>';
	}
	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return '';
	}
	$offres  = axz_offres();
	$statuts = axz_statuts();

	$offre = get_user_meta( $user_id, 'axz_offre', true );
	if ( ! isset( $offres[ $offre ] ) ) {
		$offre = 'essentiel';
	}
	$statut = get_user_meta( $user_id, 'axz_statut', true );
	if ( ! isset( $statuts[ $statut ] ) ) {
		$statut = 'essai';
	}
	$fin_essai = get_user_meta( $user_id, 'axz_fin_essai', true );
	$tel       = get_user_meta( $user_id, 'axz_telephone', true );

	$jours = null;
	if ( 'essai' === $statut && $fin_essai ) {
		$jours = (int) floor( ( strtotime( $fin_essai . ' 23:59:59' ) - time() ) / DAY_IN_SECONDS );
	}

	ob_start();
	?>
	<div class="dash-grid">
		<div class="dash-card">
			<div class="d-tag"><?php esc_html_e( 'MON OFFRE', 'axz' ); ?></div>
			<div class="d-val"><?php echo esc_html( $offres[ $offre ]['label'] ); ?></div>
			<div class="d-sub"><?php echo esc_html( axz_prix_formate( $offres[ $offre ]['prix'] ) ); ?> / <?php esc_html_e( 'mois', 'axz' ); ?></div>
		</div>
		<div class="dash-card">
			<div class="d-tag"><?php esc_html_e( 'STATUT', 'axz' ); ?></div>
			<div class="d-val"><span class="badge <?php echo esc_attr( $statut ); ?>"><?php echo esc_html( $statuts[ $statut ] ); ?></span></div>
			<div class="d-sub">
				<?php if ( 'essai' === $statut && null !== $jours ) : ?>
					<?php
					if ( $jours > 1 ) {
						/* translators: %d = nombre de jours */
						printf( esc_html__( '%d jours d\u2019essai restants', 'axz' ), (int) $jours );
					} elseif ( 1 === $jours ) {
						esc_html_e( 'Dernier jour d\u2019essai', 'axz' );
					} else {
						esc_html_e( 'Essai terminé — validation en cours', 'axz' );
					}
					?>
				<?php elseif ( 'actif' === $statut ) : ?>
					<?php esc_html_e( 'Abonnement validé. Bon travail avec Aelyra !', 'axz' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Compte suspendu — contacte DIGITALE BUSINESSE.', 'axz' ); ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="dash-card">
			<div class="d-tag"><?php esc_html_e( 'MON COMPTE', 'axz' ); ?></div>
			<div class="d-val" style="font-size:22px"><?php echo esc_html( $user->display_name ); ?></div>
			<div class="d-sub"><?php echo esc_html( $user->user_email ); ?><?php echo $tel ? '<br>' . esc_html( $tel ) : ''; ?></div>
		</div>
	</div>
	<?php if ( 'essai' === $statut ) : ?>
		<div class="dash-note">
			<?php esc_html_e( 'Tu es en essai gratuit de 7 jours. À la fin de l\u2019essai, l\u2019équipe DIGITALE BUSINESSE valide ton abonnement et connecte ton WhatsApp d\u2019entreprise.', 'axz' ); ?>
		</div>
	<?php endif; ?>
	<div class="dash-actions">
		<a class="btn btn-ghost" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Se déconnecter', 'axz' ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

/* Shortcode [axz_espace_client] — même contenu, utilisable dans une page */
function axz_shortcode_espace_client() {
	if ( ! is_user_logged_in() ) {
		return '<p>' . sprintf(
			/* translators: %s = lien de connexion */
			esc_html__( 'Connecte-toi pour voir ton espace client : %s.', 'axz' ),
			'<a href="' . esc_url( home_url( '/connexion/' ) ) . '">' . esc_html__( 'Connexion', 'axz' ) . '</a>'
		) . '</p>';
	}
	return axz_render_dashboard( get_current_user_id() );
}
add_shortcode( 'axz_espace_client', 'axz_shortcode_espace_client' );
