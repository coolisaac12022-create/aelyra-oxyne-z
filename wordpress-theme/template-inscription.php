<?php
/**
 * Template Name: Inscription Aelyra
 * Page « Inscription » : nom, e-mail, téléphone, mot de passe + choix de
 * l'offre. Crée un utilisateur au rôle « client_aelyra » (fixé côté serveur,
 * jamais depuis le formulaire), statut « essai », fin d'essai = +7 jours.
 * Formulaire protégé par nonce ; toutes les entrées sont assainies.
 */
defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) {
	wp_safe_redirect( home_url( '/espace-client/' ) );
	exit;
}

$offres     = axz_offres();
$axz_errors = array();
$axz_values = array(
	'nom'       => '',
	'email'     => '',
	'telephone' => '',
	'offre'     => 'essentiel',
);

/* Pré-sélection de l'offre depuis les boutons « Choisir » des tarifs. */
if ( isset( $_GET['offre'] ) ) {
	$o = sanitize_key( wp_unslash( $_GET['offre'] ) );
	if ( array_key_exists( $o, $offres ) ) {
		$axz_values['offre'] = $o;
	}
}

if ( isset( $_POST['axz_inscription_submit'] ) ) {
	check_admin_referer( 'axz_inscription', 'axz_inscription_nonce' );

	$nom   = isset( $_POST['axz_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['axz_nom'] ) ) : '';
	$email = isset( $_POST['axz_email'] ) ? sanitize_email( wp_unslash( $_POST['axz_email'] ) ) : '';
	$tel   = isset( $_POST['axz_telephone'] ) ? sanitize_text_field( wp_unslash( $_POST['axz_telephone'] ) ) : '';
	/* Les mots de passe ne sont pas « assainis » (cela les altérerait) :
	   ils sont dépouillés des slashes, jamais affichés, et hachés par WP. */
	$pass  = isset( $_POST['axz_pass'] ) ? wp_unslash( $_POST['axz_pass'] ) : '';
	$pass2 = isset( $_POST['axz_pass2'] ) ? wp_unslash( $_POST['axz_pass2'] ) : '';
	$offre = isset( $_POST['axz_offre'] ) ? sanitize_key( wp_unslash( $_POST['axz_offre'] ) ) : 'essentiel';

	$axz_values = array(
		'nom'       => $nom,
		'email'     => $email,
		'telephone' => $tel,
		'offre'     => array_key_exists( $offre, $offres ) ? $offre : 'essentiel',
	);

	if ( '' === $nom ) {
		$axz_errors[] = __( 'Indique ton nom.', 'axz' );
	}
	if ( ! is_email( $email ) ) {
		$axz_errors[] = __( 'E-mail invalide.', 'axz' );
	} elseif ( email_exists( $email ) ) {
		$axz_errors[] = __( 'Cet e-mail a déjà un compte. Connecte-toi.', 'axz' );
	}
	if ( strlen( $pass ) < 8 ) {
		$axz_errors[] = __( 'Mot de passe : 8 caractères minimum.', 'axz' );
	} elseif ( $pass !== $pass2 ) {
		$axz_errors[] = __( 'Les mots de passe ne correspondent pas.', 'axz' );
	}

	if ( empty( $axz_errors ) ) {
		$base  = sanitize_user( current( explode( '@', $email ) ), true );
		$login = '' !== $base ? $base : 'client';
		$try   = $login;
		$i     = 0;
		while ( username_exists( $try ) ) {
			$i++;
			$try = $login . $i;
		}

		$user_id = wp_insert_user( array(
			'user_login'   => $try,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $nom,
			/* Rôle imposé par le code : aucune auto-promotion possible. */
			'role'         => 'client_aelyra',
		) );

		if ( is_wp_error( $user_id ) ) {
			$axz_errors[] = __( 'Inscription impossible pour le moment. Réessaie.', 'axz' );
		} else {
			update_user_meta( $user_id, 'axz_offre', $axz_values['offre'] );
			update_user_meta( $user_id, 'axz_statut', 'essai' );
			$duree_essai = function_exists( 'axz_get_option' ) ? absint( axz_get_option( 'duree_essai', 7 ) ) : 7;
			if ( $duree_essai < 1 ) {
				$duree_essai = 7;
			}
			update_user_meta( $user_id, 'axz_fin_essai', gmdate( 'Y-m-d', strtotime( '+' . $duree_essai . ' days' ) ) );
			update_user_meta( $user_id, 'axz_telephone', $tel );

			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
			wp_safe_redirect( home_url( '/espace-client/' ) );
			exit;
		}
	}
}

get_header();
?>
<section class="page-wrap">
  <div class="wrap">
    <div class="form-card">
      <h2><?php esc_html_e( 'Crée ton compte', 'axz' ); ?></h2>
      <p class="form-sub"><?php esc_html_e( '7 jours d\u2019essai gratuit, sans carte. Choisis ton offre, on s\u2019occupe du reste.', 'axz' ); ?></p>

      <?php if ( ! empty( $axz_errors ) ) : ?>
        <div class="form-err" role="alert">
          <strong><?php esc_html_e( 'Vérifie ces points :', 'axz' ); ?></strong>
          <ul>
            <?php foreach ( $axz_errors as $err ) : ?>
              <li><?php echo esc_html( $err ); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <?php wp_nonce_field( 'axz_inscription', 'axz_inscription_nonce' ); ?>

        <div class="field">
          <label for="axz_nom"><?php esc_html_e( 'Ton nom', 'axz' ); ?></label>
          <input type="text" id="axz_nom" name="axz_nom" required autocomplete="name"
                 value="<?php echo esc_attr( $axz_values['nom'] ); ?>">
        </div>

        <div class="field">
          <label for="axz_email"><?php esc_html_e( 'E-mail', 'axz' ); ?></label>
          <input type="email" id="axz_email" name="axz_email" required autocomplete="email"
                 value="<?php echo esc_attr( $axz_values['email'] ); ?>">
        </div>

        <div class="field">
          <label for="axz_telephone"><?php esc_html_e( 'Téléphone (WhatsApp d\u2019entreprise)', 'axz' ); ?></label>
          <input type="tel" id="axz_telephone" name="axz_telephone" autocomplete="tel"
                 value="<?php echo esc_attr( $axz_values['telephone'] ); ?>">
          <p class="hint"><?php esc_html_e( 'Le numéro qu\u2019Aelyra utilisera pour ton entreprise.', 'axz' ); ?></p>
        </div>

        <div class="field">
          <label for="axz_pass"><?php esc_html_e( 'Mot de passe', 'axz' ); ?></label>
          <input type="password" id="axz_pass" name="axz_pass" required autocomplete="new-password"
                 minlength="8">
          <p class="hint"><?php esc_html_e( '8 caractères minimum.', 'axz' ); ?></p>
        </div>

        <div class="field">
          <label for="axz_pass2"><?php esc_html_e( 'Confirme le mot de passe', 'axz' ); ?></label>
          <input type="password" id="axz_pass2" name="axz_pass2" required autocomplete="new-password"
                 minlength="8">
        </div>

        <div class="field">
          <label><?php esc_html_e( 'Ton offre', 'axz' ); ?></label>
          <div class="offre-pick">
            <?php foreach ( $offres as $key => $o ) : ?>
              <label class="offre-opt">
                <input type="radio" name="axz_offre" value="<?php echo esc_attr( $key ); ?>"
                       <?php checked( $axz_values['offre'], $key ); ?>>
                <span>
                  <span class="o-name"><?php echo esc_html( $o['label'] ); ?></span>
                  <span class="o-desc"><?php echo esc_html( $o['desc'] ); ?></span>
                </span>
                <span class="o-price"><?php echo esc_html( axz_prix_formate( $o['prix'] ) ); ?>/mois</span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" name="axz_inscription_submit" class="btn btn-terra">
          <?php esc_html_e( 'Lancer mon essai gratuit', 'axz' ); ?>
        </button>
      </form>

      <p class="form-alt">
        <?php esc_html_e( 'Déjà un compte ?', 'axz' ); ?>
        <a href="<?php echo esc_url( home_url( '/connexion/' ) ); ?>"><?php esc_html_e( 'Connecte-toi.', 'axz' ); ?></a>
      </p>
    </div>
  </div>
</section>
<?php
get_footer();
