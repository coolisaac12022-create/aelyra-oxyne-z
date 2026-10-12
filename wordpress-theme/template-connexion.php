<?php
/**
 * Template Name: Connexion Aelyra
 * Page « Connexion » : formulaire de connexion WordPress, redirigé vers
 * l'espace client après login. La limitation des tentatives (5 / 15 min)
 * est gérée par inc/security.php via le filtre 'authenticate'.
 */
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/espace-client/' ) );
	exit;
}

get_header();
?>
<section class="page-wrap">
  <div class="wrap">
    <div class="form-card">
      <h2><?php esc_html_e( 'Connexion', 'axz' ); ?></h2>
      <p class="form-sub"><?php esc_html_e( 'Accède à ton espace client Aelyra.', 'axz' ); ?></p>
      <?php
      wp_login_form( array(
        'redirect'       => home_url( '/espace-client/' ),
        'label_username' => __( 'E-mail ou identifiant', 'axz' ),
        'label_password' => __( 'Mot de passe', 'axz' ),
        'label_log_in'   => __( 'Se connecter', 'axz' ),
        'remember'       => true,
      ) );
      ?>
      <p class="form-alt">
        <?php esc_html_e( 'Pas encore de compte ?', 'axz' ); ?>
        <a href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>"><?php esc_html_e( 'Crée ton compte et lance ton essai gratuit.', 'axz' ); ?></a>
      </p>
    </div>
  </div>
</section>
<?php
get_footer();
