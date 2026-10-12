<?php
/**
 * Aelyra Oxyne Z — page 404.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-wrap">
  <div class="wrap narrow" style="text-align:center">
    <h1 class="page-title">404</h1>
    <div class="page-content">
      <p><?php esc_html_e( 'Cette page n\u2019existe pas ou a été déplacée.', 'axz' ); ?></p>
      <p style="margin-top:22px"><a class="btn btn-terra" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l\u2019accueil', 'axz' ); ?></a></p>
    </div>
  </div>
</section>
<?php
get_footer();
