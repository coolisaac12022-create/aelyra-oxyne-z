# Aelyra Oxyne Z — Thème WordPress (v1.0.0)

Thème officiel du SaaS **Aelyra × Oxyne Z** (DIGITALE BUSINESSE) : vitrine publique
(charte « D'ici »), inscription avec essai gratuit de 7 jours, espace client et
tableau de bord administrateur.

## Contenu

- **Vitrine** : `front-page.php` reprend toutes les sections publiques du site
  (héros + slogan « Vos clients accueillis. Votre nom protégé. », le principe,
  Aelyra, Comment ça marche, la vue du patron, Oxyne Z, les environnements,
  les tarifs, Confiance et données, FAQ) avec les **vrais prix décidés** :
  Essentiel 6 000 F/mois, Pro 10 000 F/mois, Premium 60 000 F/mois.
- **Connexion** (`template-connexion.php`), **Inscription**
  (`template-inscription.php`, rôle `client_aelyra`, statut `essai`, fin
  d'essai = +7 jours), **Espace client** (`template-espace-client.php` +
  shortcode `[axz_espace_client]`).
- **Admin « Aelyra »** : Vue d'ensemble (clients, essais en cours, répartition
  par offre, MRR estimé), Clients (liste avec recherche, pagination, actions
  Valider / Suspendre / Réactiver), Abonnements (changement d'offre et de statut).
- **Sécurité** : garde `ABSPATH` sur chaque fichier, `current_user_can()` sur
  chaque page d'admin et chaque action, nonces sur tous les formulaires,
  entrées assainies, sorties échappées, anti-référence directe d'objet (un
  client ne voit que ses propres données), limitation des connexions
  (5 essais / 15 min par IP), clients redirigés hors de wp-admin, barre
  d'admin masquée pour les clients.

## Installation

1. Zippe le dossier `wordpress-theme` (le zip doit contenir les fichiers à sa
   racine, pas un sous-dossier).
2. Dans WordPress : **Apparence > Thèmes > Ajouter > Téléverser un thème**,
   puis **Activer**.
3. L'activation crée le rôle `client_aelyra` et attribue les capacités
   `axz_view_dashboard`, `axz_manage_clients`, `axz_manage_subscriptions`
   aux administrateurs.

## Réglages

1. **Permaliens** : Réglages > Permaliens > « Titre de la publication ».
2. **Pages** : crée 3 pages et assigne-leur leur modèle (menu déroulant
   « Modèle » à droite dans l'éditeur) :
   - `Connexion` (permalien `/connexion/`) → modèle **Connexion Aelyra**
   - `Inscription` (permalien `/inscription/`) → modèle **Inscription Aelyra**
   - `Espace client` (permalien `/espace-client/`) → modèle **Espace client Aelyra**
   (Le shortcode `[axz_espace_client]` affiche aussi le tableau de bord dans
   n'importe quelle page.)
3. **Accueil** : Réglages > Lecture > « Une page statique » (crée une page
   vide « Accueil » si besoin) — la vitrine s'affiche automatiquement.
4. **Menus** : Apparence > Menus — le thème déclare « Menu principal » et
   « Menu pied de page » (le pied de page du site affiche ce dernier).

## wp-config.php recommandé

```php
define( 'DISALLOW_FILE_EDIT', true ); // pas d'édition de fichiers depuis l'admin
define( 'FORCE_SSL_ADMIN', true );     // admin + connexion en HTTPS
```

## Sécurité — à faire par toi

- **Active la double authentification (2FA)** sur ton compte administrateur
  (extension ou réglages de ton hébergeur) : c'est la porte d'entrée du site.
- Utilise un mot de passe long et unique pour l'admin, jamais réutilisé.
- Fais des sauvegardes régulières (fichiers + base de données) et teste leur
  restauration une fois.

## Note honnête

Ce thème est la **vitrine + la gestion des clients** (inscription, essais,
abonnements, espace client). Les agents eux-mêmes — Aelyra sur WhatsApp et
la veille d'Oxyne Z — tourneront sur une infrastructure séparée et se
brancheront ensuite au site via API (webhooks WhatsApp vérifiés, clés
conservées hors du code). Rien dans ce thème ne simule un agent : la
démonstration animée de la page d'accueil est étiquetée comme telle.
