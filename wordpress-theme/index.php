<?php
/**
 * Aelyra Oxyne Z — liste des articles (blog / actualités).
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="page-wrap">
  <div class="wrap narrow">
    <h1 class="page-title"><?php esc_html_e( 'Actualités', 'axz' ); ?></h1>
    <?php if ( have_posts() ) : ?>
      <?php while ( have_posts() ) : the_post(); ?>
        <article style="margin-bottom:34px;padding-bottom:34px;border-bottom:1px solid var(--line-soft)">
          <h2 style="font-size:26px"><a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit"><?php the_title(); ?></a></h2>
          <p style="color:var(--ink-soft);font-size:14px;margin:6px 0 10px"><?php echo esc_html( get_the_date() ); ?></p>
          <div class="page-content"><?php the_excerpt(); ?></div>
        </article>
      <?php endwhile; ?>
      <div class="page-content">
        <?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
      </div>
    <?php else : ?>
      <div class="page-content"><p><?php esc_html_e( 'Aucun article pour le moment.', 'axz' ); ?></p></div>
    <?php endif; ?>
  </div>
</section>
<?php
get_footer();
