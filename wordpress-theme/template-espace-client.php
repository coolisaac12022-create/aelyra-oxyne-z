<?php
/**
 * Template Name: Espace client Aelyra
 * Page « Espace client » : réservée aux connectés. Affiche l'offre, le statut
 * et les jours d'essai restants — STRICTEMENT les données de l'utilisateur
 * connecté (comparaison avec get_current_user_id(), jamais un ID fourni).
 * Le même contenu est disponible via le shortcode [axz_espace_client].
 */
defined( 'ABSPATH' ) || exit;

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/connexion/' ) );
	exit;
}

get_header();
?>
<section class="page-wrap">
  <div class="wrap">
    <h1 class="page-title"><?php esc_html_e( 'Mon espace client', 'axz' ); ?></h1>
    <?php echo axz_render_dashboard( get_current_user_id() ); ?>
  </div>
</section>
<?php
get_footer();
