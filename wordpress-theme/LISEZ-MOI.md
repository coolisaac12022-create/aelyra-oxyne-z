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
  par offre, MRR estimé, **alertes de dépassement des plafonds de coût**),
  Clients (liste avec recherche, pagination, actions Valider / Suspendre /
  Réactiver), Abonnements (changement d'offre et de statut), **Essais**
  (essais finissant dans ≤ 3 jours + bouton « Marquer relancé »),
  **Paiements** (statut manuel par client : payé / en attente), **Alertes
  Oxyne Z** (structure prête : validation/rejet des signalements),
  **Journal** (journal d'activité paginé, filtre par client),
  **Réglages** (clés API, plafonds de coût, durée d'essai, devise).
- **Sécurité** : garde `ABSPATH` sur chaque fichier, `current_user_can()` sur
  chaque page d'admin et chaque action, nonces sur tous les formulaires,
  entrées assainies, sorties échappées, anti-référence directe d'objet (un
  client ne voit que ses propres données), limitation des connexions
  (5 essais / 15 min par IP), clients redirigés hors de wp-admin, barre
  d'admin masquée pour les clients.
- **Extensions admin** : journal d'activité en table dédiée (qui a fait quoi,
  quand), statuts de paiement validés manuellement (pas de prélèvement auto),
  alertes Oxyne Z avec validation obligatoire avant tout signalement,
  plafonds de coût par client avec alerte de dépassement, clés API stockées
  en options et masquées à l'affichage.

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
5. **Aelyra > Réglages** (administrateur uniquement) : à remplir **quand le
   financement arrive** — le site fonctionne sans, en mode vitrine +
   gestion manuelle :
   - **Clés API** : jeton WhatsApp Business, identifiant du numéro WhatsApp,
     clé API du modèle d'IA, clé client TikTok (stockées masquées, jamais
     affichées en clair).
   - **Plafonds de coût** : plafond WhatsApp et plafond IA ($/mois/client,
     défauts 10 et 5) — la vue d'ensemble alerte en cas de dépassement dès
     que le connecteur agent remonte la consommation.
   - **Général** : durée d'essai en jours (défaut 7, appliquée aux nouvelles
     inscriptions), devise (défaut « F CFA »).

## wp-config.php recommandé

```php
define( 'DISALLOW_FILE_EDIT', true ); // pas d'édition de fichiers depuis l'admin
define( 'FORCE_SSL_ADMIN', true );     // admin + connexion en HTTPS
```

## Sécurité intégrée au thème

Le thème embarque un module de sécurité sérieux (les attaques contre les
vibe codeurs sont fréquentes) :

- **Journal de sécurité** (`Aelyra > Sécurité`, administrateur uniquement) :
  chaque tentative d'attaque est enregistrée avec date, adresse IP et
  navigateur — connexion échouée, accès interdit, nonce invalide, bot
  piégé par le honeypot, requête suspecte, IP bloquée, XML-RPC bloqué.
  Conservation : 90 jours (purge automatique quotidienne).
- **Blocage automatique** : 10 échecs de connexion en 1 heure depuis la même
  IP → IP bloquée 1 heure (page 403 + journal). Blocage/déblocage manuel
  possible depuis le journal.
- **Alerte e-mail** : si une IP génère 10 événements en 1 heure,
  l'administrateur reçoit un e-mail « [Aelyra] Activité suspecte détectée ».
  L'envoi dépend de l'hébergeur : le journal reste la source fiable.
- **Honeypot anti-bot** : champ invisible sur les formulaires de connexion
  et d'inscription — un bot qui le remplit est journalisé et bloqué (403).
- **XML-RPC désactivé** (vecteur de force brute) + 403 journalisé sur accès
  direct à `xmlrpc.php`.
- **Énumération d'utilisateurs bloquée** : `?author=N` redirigé vers
  l'accueil + journalisé ; listage `/wp/v2/users` masqué aux visiteurs.
- **Requêtes suspectes** : les URL contenant `../`, `wp-config`, `.env`,
  `.git` sont bloquées en 403 et journalisées ; `<script`, `UNION SELECT`,
  `eval(` sont journalisés.
- **Nonces journalisés** : tout échec de nonce dans l'admin écrit une ligne
  `nonce_invalide` avant le refus (même comportement qu'avant, avec preuve).
- **En-têtes HTTP** : `X-Content-Type-Options: nosniff`,
  `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy:
  strict-origin-when-cross-origin` ; version de WordPress masquée.
- **Limites** : limitation de connexion 5 essais / 15 min par IP (déjà
  présente), clients redirigés hors de `wp-admin` (tentative journalisée),
  rôles et capacités stricts (voir `inc/roles.php`).

Limite honnête : une adresse IP ne permet **pas** d'identifier la personne
derrière une attaque (VPN, proxy, IP partagée). Les journaux servent de
preuves pour les autorités ou l'hébergeur — jamais de « hack back ».

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
