<?php
/**
 * Aelyra Oxyne Z — header.
 * La navigation reprend celle du site public ; les ancres pointent vers
 * la page d'accueil quand on n'y est pas. Le CSS reste encapsulé sous .axz.
 */
defined( 'ABSPATH' ) || exit;

$is_front = is_front_page();
$anchor   = function ( $id ) use ( $is_front ) {
	return $is_front ? '#' . $id : esc_url( home_url( '/#' . $id ) );
};
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="axz">
<div class="progress" id="axzProgress"></div>

<nav class="nav">
  <div class="wrap nav-in">
    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="text-decoration:none;color:inherit">DIGITALE <b>BUSINESSE</b></a>
    <button class="burger" id="axzBurger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="axzNavlinks"><span></span><span></span><span></span></button>
    <div class="navlinks" id="axzNavlinks">
      <a href="<?php echo $anchor( 'principe' ); ?>">Le principe</a>
      <a href="<?php echo $anchor( 'aelyra' ); ?>">Aelyra</a>
      <a href="<?php echo $anchor( 'fonctionnement' ); ?>">Comment ça marche</a>
      <a href="<?php echo $anchor( 'oxyne' ); ?>">Oxyne Z</a>
      <a href="<?php echo $anchor( 'tarifs' ); ?>">Tarifs</a>
      <a href="<?php echo $anchor( 'faq' ); ?>">FAQ</a>
      <?php if ( is_user_logged_in() ) : ?>
        <a class="nav-login" href="<?php echo esc_url( home_url( '/espace-client/' ) ); ?>">Mon espace</a>
      <?php else : ?>
        <a class="nav-login" href="<?php echo esc_url( home_url( '/connexion/' ) ); ?>">Connexion</a>
      <?php endif; ?>
    </div>
    <a class="btn btn-terra btn-nav" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>">Essayer gratuitement</a>
  </div>
</nav>
