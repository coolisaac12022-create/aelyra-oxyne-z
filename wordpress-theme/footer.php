<?php
/**
 * Aelyra Oxyne Z — footer (reprend le pied de page du site public).
 */
defined( 'ABSPATH' ) || exit;
?>
<footer>
  <div class="wrap">
    <div class="f-grid">
      <div>
        <div class="f-brand">DIGITALE <span>BUSINESSE</span></div>
        <p>L'équipe d'agents des PME : Aelyra accueille vos clients, Oxyne Z protège votre nom. Pensée ici, pour les entreprises d'ici.</p>
      </div>
      <div class="f-contact">
        WHATSAPP PROFESSIONNEL<br><b>05-95-53-28-84</b>
      </div>
    </div>
    <nav class="f-menu" aria-label="<?php esc_attr_e( 'Menu pied de page', 'axz' ); ?>">
      <?php wp_nav_menu( array( 'theme_location' => 'pied', 'container' => false, 'fallback_cb' => false ) ); ?>
    </nav>
    <div class="f-bottom">
      <span>© 2026 DIGITALE BUSINESSE — TOUS DROITS RÉSERVÉS</span>
      <span>AELYRA × OXYNE Z</span>
    </div>
  </div>
</footer>
</div><!-- /.axz -->
<?php wp_footer(); ?>
</body>
</html>
