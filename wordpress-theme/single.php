<?php
/**
 * Aelyra Oxyne Z — article seul.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-wrap">
  <div class="wrap narrow">
    <?php while ( have_posts() ) : the_post(); ?>
      <h1 class="page-title"><?php the_title(); ?></h1>
      <p style="color:var(--ink-soft);font-size:14px;margin-bottom:22px"><?php echo esc_html( get_the_date() ); ?></p>
      <div class="page-content"><?php the_content(); ?></div>
    <?php endwhile; ?>
  </div>
</section>
<?php
get_footer();
