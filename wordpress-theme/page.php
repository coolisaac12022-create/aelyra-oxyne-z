<?php
/**
 * Aelyra Oxyne Z — modèle de page par défaut.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-wrap">
  <div class="wrap narrow">
    <?php while ( have_posts() ) : the_post(); ?>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <div class="page-content"><?php the_content(); ?></div>
    <?php endwhile; ?>
  </div>
</section>
<?php
get_footer();
