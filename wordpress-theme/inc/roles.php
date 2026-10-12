<?php
/**
 * Aelyra Oxyne Z — rôles et capacités.
 *
 * - Rôle « client_aelyra » : lecture seule (read), créé à l'inscription.
 * - Capacités personnalisées, attribuées UNIQUEMENT aux administrateurs :
 *   axz_view_dashboard, axz_manage_clients, axz_manage_subscriptions.
 * - Le rôle d'un utilisateur n'est JAMAIS déduit d'un formulaire :
 *   l'inscription force 'client_aelyra' côté serveur.
 */
defined( 'ABSPATH' ) || exit;

function axz_add_roles_caps() {
	add_role(
		'client_aelyra',
		__( 'Client Aelyra', 'axz' ),
		array( 'read' => true )
	);

	$admin = get_role( 'administrator' );
	if ( $admin ) {
		foreach ( array( 'axz_view_dashboard', 'axz_manage_clients', 'axz_manage_subscriptions' ) as $cap ) {
			$admin->add_cap( $cap );
		}
	}
}
add_action( 'after_switch_theme', 'axz_add_roles_caps' );

/* Filet de sécurité : si les capacités ont été perdues (import, etc.),
   on les réattribue aux administrateurs. */
function axz_ensure_admin_caps() {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}
	foreach ( array( 'axz_view_dashboard', 'axz_manage_clients', 'axz_manage_subscriptions' ) as $cap ) {
		if ( ! $admin->has_cap( $cap ) ) {
			$admin->add_cap( $cap );
		}
	}
}
add_action( 'admin_init', 'axz_ensure_admin_caps' );
