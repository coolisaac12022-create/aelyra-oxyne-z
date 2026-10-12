# Aelyra × Oxyne Z

Le site public du SaaS de DIGITALE BUSINESSE pour les PME.

- **Aelyra** — la réceptionniste des entreprises : elle répond aux clients sur WhatsApp, trie les messages par priorité (sans jamais en ignorer un), prend les rendez-vous et envoie les liens de paiement Mobile Money. Elle dit toujours la vérité : c'est une IA.
- **Oxyne Z** — l'agent de sécurité (offre premium) : il surveille l'usurpation de l'entreprise (fausses pages, faux comptes, numéros détournés), prouve ce qu'il trouve, et ne signale jamais rien sans l'accord du propriétaire.

Chaque entreprise cliente a son propre environnement : les données sont cloisonnées, jamais mélangées.

## Le site

`index.html` — le site complet en un seul fichier (charte « D'ici » : terracotta, crème, encre ; Fraunces + Archivo), avec la démonstration animée du tri des messages par Aelyra et le panneau de veille d'Oxyne Z. Aucune dépendance à installer : ouvrir le fichier suffit.

`charte.html` — la charte graphique complète : nuancier cliquable (codes hex copiés au clic), spécimens Fraunces et Archivo, composants réels, règles de mouvement et de ton.

## Lien public (GitHub Pages)

Une fois GitHub Pages activé pour ce dépôt (branche `main`, racine), le site est visible à :
https://coolisaac12022-create.github.io/aelyra-oxyne-z/
et la charte à :
https://coolisaac12022-create.github.io/aelyra-oxyne-z/charte.html

## État

Version publique de présentation. Le lancement du service suivra la mise en place de l'infrastructure (hébergement, accès WhatsApp Business, paiements).

## Thème WordPress

`wordpress-theme/` — le thème WordPress « Aelyra Oxyne Z » (tarifs réels : Essentiel 6 000 F, Pro 10 000 F, Premium 60 000 F ; slogan « Vos clients accueillis. Votre nom protégé. »). Contient : la vitrine (charte « D'ici »), les pages Connexion / Inscription (essai 7 jours) / Espace client, et le tableau admin « Aelyra » (vue d'ensemble, clients, abonnements) durci contre les failles de contrôle d'accès (Broken Access Control) : capacités personnalisées, nonces, requêtes préparées. Installation : zipper le dossier `wordpress-theme` et l'envoyer via Apparence > Thèmes > Ajouter, puis suivre `wordpress-theme/LISEZ-MOI.md`.

---
DIGITALE BUSINESSE — WhatsApp professionnel : 05-95-53-28-84
