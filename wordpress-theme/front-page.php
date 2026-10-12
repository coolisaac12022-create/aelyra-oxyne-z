<?php
/**
 * Aelyra Oxyne Z — page d'accueil (vitrine publique).
 * Sections : héros + slogan, principe, Aelyra, fonctionnement,
 * vue du patron, Oxyne Z, environnements, tarifs réels, confiance, FAQ.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>


<header class="hero">
  <div class="wrap">
    <span class="eyebrow rv"><span class="dot"></span> UNE ÉQUIPE D'AGENTS POUR LES PME</span>
    <div class="hero-grid">
      <div>
        <h1 class="rv rv-d1">L'une accueille.<br>L'autre <em>veille.</em></h1>
        <p class="lede rv rv-d2">AELYRA répond aux clients de l'entreprise sur WhatsApp, jour et nuit. OXYNE Z surveille son nom et son numéro, et alerte le propriétaire dès qu'un faux compte apparaît. <strong>Deux agents, deux métiers, deux environnements — qui ne se mélangent jamais.</strong></p>
        <p class="hero-slogan rv rv-d2">Vos clients accueillis. <em>Votre nom protégé.</em></p>
        <div class="hero-cta rv rv-d3">
          <a class="btn btn-terra" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>">Essayer gratuitement</a>
          <a class="btn btn-ghost" href="#aelyra">Voir Aelyra travailler</a>
        </div>
        <p class="hero-note rv rv-d3">7 jours d'essai gratuit, sans carte — on connecte ton WhatsApp, Aelyra répond à tes clients.</p>
        <div class="hero-stats rv rv-d4">
          <div class="stat"><div class="num"><span data-count="2">0</span></div><div class="lbl">agents spécialisés, un seul abonnement</div></div>
          <div class="stat"><div class="num"><span data-count="7">0</span> <i>jours</i></div><div class="lbl">d'essai gratuit, sans carte</div></div>
          <div class="stat"><div class="num"><span data-count="1">0</span></div><div class="lbl">environnement rien qu'à toi, par commerçant</div></div>
        </div>
      </div>
      <div class="mark-card rv rv-d2">
        <svg width="150" height="86" viewBox="0 0 150 86" fill="none" aria-hidden="true">
          <path class="wavepath" d="M8 46 C 26 18, 40 18, 52 42 C 62 62, 74 62, 84 42 C 96 18, 112 18, 142 40" stroke="#C96F3F" stroke-width="7" stroke-linecap="round"/>
          <circle class="wavedot" cx="75" cy="40" r="7.5" fill="#1C1A17"/>
        </svg>
        <div class="wordmark">AELYRA</div>
        <div class="tagline">« La réceptionniste des entreprises. »</div>
        <div class="micro">
          TERRE CUITE · CRÈME · ENCRE — SERIF ÉLÉGANT<br>
          SIGNÉE DIGITALE BUSINESSE
        </div>
      </div>
    </div>
  </div>
</header>

<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <span>Accueil <i>●</i> Réponse <i>●</i> Veille <i>●</i> Confiance <i>●</i> Vérité <i>●</i> Proximité <i>●</i></span>
    <span>Accueil <i>●</i> Réponse <i>●</i> Veille <i>●</i> Confiance <i>●</i> Vérité <i>●</i> Proximité <i>●</i></span>
  </div>
</div>

<section id="principe">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">01 — LE PRINCIPE</div>
      <h2>Comme une conversation avec <em>plusieurs personnes :</em> chacun a son monde.</h2>
      <p>On ne parle pas de la même façon à tout le monde, et on ne mélange pas les conversations. C'est pareil ici : chaque agent a son environnement, ses règles, son ton. Et chaque commerçant a le sien.</p>
    </div>
    <div class="duo">
      <article class="agent-card card-aelyra rv">
        <div class="env-tag">ENVIRONNEMENT N°1 — LE JOUR</div>
        <h3>AELYRA</h3>
        <div class="role">l'accueil, la voix de la maison</div>
        <p>Elle répond aux clients sur WhatsApp, pour les salons de coiffure, les agences, les entreprises. Chaleureuse, claire, toujours polie — comme quelqu'un de la maison.</p>
        <ul>
          <li>Répond d'abord aux demandes sérieuses</li>
          <li>Reçoit les rendez-vous, présente le catalogue</li>
          <li>Envoie le lien de paiement Mobile Money</li>
          <li>Dit la vérité : elle est une intelligence artificielle</li>
        </ul>
      </article>
      <article class="agent-card card-oxyne rv rv-d1">
        <div class="premium-chip">PREMIUM</div>
        <div class="env-tag">ENVIRONNEMENT N°2 — LA NUIT</div>
        <h3>OXYNE Z</h3>
        <div class="role">le SOC, l'œil qui ne cligne pas</div>
        <p>Il surveille les réseaux pour l'entreprise : fausses pages qui copient son nom, numéros de malfaiteurs qui se font passer pour elle. Il détecte, il alerte, il prépare le signalement.</p>
        <ul>
          <li>Détecte les usurpations d'entreprise</li>
          <li>Alerte le propriétaire dès la détection</li>
          <li>Prépare le signalement pour Facebook, WhatsApp, Instagram</li>
          <li>Prépare le dossier de preuves pour la plainte</li>
        </ul>
      </article>
    </div>
    <div class="principle-note rv">
      La règle d'or de la maison : <b>Aelyra ne fait pas le travail d'Oxyne Z, Oxyne Z ne répond pas aux clients.</b> Deux agents qui se mélangent, c'est un agent confus. Deux agents séparés, c'est une équipe.
    </div>
  </div>
</section>

<section id="aelyra" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">02 — ENVIRONNEMENT N°1</div>
      <h2>Aelyra, dans <em>son</em> environnement : l'accueil.</h2>
      <p>Son monde, c'est la conversation avec les clients de l'entreprise, sur WhatsApp. Regarde-la travailler : elle reçoit tout, elle trie, elle répond dans l'ordre — et elle revient vers tout le monde.</p>
    </div>
    <div class="split">
      <div class="rules rv">
        <div class="rule">
          <div class="n">R1</div>
          <div>
            <h4>Elle trie avant de répondre</h4>
            <p>Un message qui demande un prix, un rendez-vous, une commande : c'est une intention claire. Il passe devant. Les conversations sérieuses ne font pas la queue derrière les salutations.</p>
          </div>
        </div>
        <div class="rule">
          <div class="n">R2</div>
          <div>
            <h4>Personne n'est ignoré</h4>
            <p>Les « cc », « salut », « bonsoir » sont mis de côté — pas jetés. Quand les échanges importants sont traités, elle revient vers chacun et ouvre la conversation.</p>
          </div>
        </div>
        <div class="rule">
          <div class="n">R3</div>
          <div>
            <h4>Elle dit la vérité</h4>
            <p>Dès son premier message, elle se présente : la réceptionniste de la maison — et une intelligence artificielle. Pas de faux semblant, jamais.</p>
          </div>
        </div>
        <div class="rule">
          <div class="n">R4</div>
          <div>
            <h4>Elle sait passer la main</h4>
            <p>Une question qu'elle ne peut pas traiter ? Elle transmet au patron, avec le résumé de la conversation. Le client n'a jamais à tout répéter.</p>
          </div>
        </div>
      </div>
      <div class="rv rv-d1">
        <div class="phone" id="axzPhone">
          <div class="phone-screen">
            <div class="chat-head">
              <div class="avatar">A</div>
              <div class="who"><b>Salon « Chez Aïcha »</b><span>Aelyra répond · en ligne</span></div>
            </div>
            <div class="chat-body" id="axzChatBody">
              <div class="msg in"><div class="meta"><span class="from">Cliente</span><span class="prio hot">PRIORITAIRE</span></div>Bonjour, la tresse africaine c'est combien ? Et vous avez une place samedi ?<span class="time">09:02</span></div>
              <div class="msg out"><div class="meta"><span class="prio info">AELYRA</span></div>Bonjour, je suis Aelyra, la réceptionniste des entreprises — je suis une IA, au service du salon Chez Aïcha. La tresse africaine est à partir de 8 000 F. Samedi, il reste une place à 10h30 — je vous la bloque ?<span class="time">09:02</span></div>
              <div class="msg in"><div class="meta"><span class="from">Nouveau client</span><span class="prio wait">MIS DE CÔTÉ</span></div>cc 👋<span class="time">09:03</span></div>
              <div class="msg in"><div class="meta"><span class="from">Cliente</span><span class="prio hot">PRIORITAIRE</span></div>Oui, bloquez. Je paie l'acompte comment ?<span class="time">09:04</span></div>
              <div class="msg out"><div class="meta"><span class="prio info">AELYRA</span></div>C'est réservé pour samedi 10h30. Voici le lien pour l'acompte de 2 000 F, payable par Orange Money, Wave ou MTN : <u>paiement.chez-aicha</u><span class="time">09:04</span></div>
              <div class="msg out"><div class="meta"><span class="prio info">AELYRA REVIENT</span></div>Bonjour ! Je suis Aelyra, la réceptionniste du salon — une intelligence artificielle. Comment puis-je vous aider ?<span class="time">09:06</span></div>
              <div class="typing" id="axzTyping"><i></i><i></i><i></i></div>
            </div>
            <div class="chat-foot"><div class="fake-input">Écrire un message…</div><div class="send">➤</div></div>
          </div>
        </div>
        <div class="chat-caption">DÉMONSTRATION ANIMÉE — VUE DU COMMERÇANT :<br>LES ÉTIQUETTES « PRIORITAIRE / MIS DE CÔTÉ » SONT CE QUE LE PATRON VOIT ; LE CLIENT, LUI, REÇOIT JUSTE LES RÉPONSES.</div>
      </div>
    </div>
  </div>
</section>

<section id="fonctionnement" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">03 — COMMENT ÇA MARCHE</div>
      <h2>En place en <em>trois temps.</em></h2>
      <p>Pas de technique à apprendre, pas de logiciel à installer chez toi. Tu décris ton entreprise, on branche, Aelyra travaille.</p>
    </div>
    <div class="steps">
      <div class="step rv">
        <div class="s-n">1</div>
        <h4>Tu remplis ta fiche entreprise</h4>
        <p>Tes services, tes prix, tes horaires, ton numéro officiel. Dix minutes, une seule fois — c'est la matière avec laquelle Aelyra répondra, sans jamais inventer.</p>
      </div>
      <div class="step rv rv-d1">
        <div class="s-n">2</div>
        <h4>On connecte ton WhatsApp</h4>
        <p>Ton numéro d'entreprise est relié à ton environnement. Tes anciens échanges ne partent nulle part : tout ce qui arrive ensuite reste chez toi.</p>
      </div>
      <div class="step rv rv-d2">
        <div class="s-n">3</div>
        <h4>Aelyra répond à tes clients</h4>
        <p>Elle trie, elle répond, elle prend les rendez-vous et envoie les liens de paiement. Toi, tu gardes la main : elle te transmet ce qui dépasse son rôle, avec le résumé.</p>
      </div>
    </div>
  </div>
</section>

<section id="patron" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">04 — LA VUE DU PATRON</div>
      <h2>Ce que tu achètes, c'est <em>ce message du matin.</em></h2>
      <p>Chaque matin, avant même d'ouvrir la boutique, Aelyra t'envoie ton résumé sur WhatsApp : la journée qui t'attend, ce qui demande ta décision, et ce qu'elle a déjà réglé.</p>
    </div>
    <div class="split">
      <div class="patron-list rv">
        <div class="rule">
          <div class="n">☀</div>
          <div>
            <h4>Tes rendez-vous du jour</h4>
            <p>Qui vient, à quelle heure, pour quoi — et si l'acompte est déjà payé. Tu sais comment ta journée est remplie avant de commencer.</p>
          </div>
        </div>
        <div class="rule">
          <div class="n">✋</div>
          <div>
            <h4>Les demandes qui t'attendent</h4>
            <p>Ce qu'Aelyra n'a pas tranché seule : une demande spéciale, un prix à discuter, un client à rappeler. Rien ne se perd dans le fil des messages.</p>
          </div>
        </div>
        <div class="rule">
          <div class="n">✓</div>
          <div>
            <h4>Ce qu'elle a traité pour toi</h4>
            <p>Les questions simples, les rendez-vous pris, les liens de paiement envoyés pendant que tu dormais ou que tu travaillais. Tu vois le travail fait, noir sur blanc.</p>
          </div>
        </div>
      </div>
      <div class="rv rv-d1">
        <div class="report-card">
          <div class="report-head">
            <div class="avatar">A</div>
            <div class="who"><b>Aelyra → la patronne</b><span>Rapport du matin · 07:00</span></div>
          </div>
          <div class="report-msg">
            <h5>☀️ Bonjour Aïcha, voici ta journée :</h5>
            <ul>
              <li><b>Rendez-vous :</b> 10h30 — tresse africaine (acompte reçu) ; 14h00 — soin + pose.</li>
              <li><b>En attente de ta réponse :</b> 2 clientes pour des tissages — je t'ai préparé leurs messages.</li>
              <li><b>Traité cette nuit :</b> 9 conversations répondues, 3 rendez-vous pris, aucun message laissé de côté.</li>
            </ul>
            <span class="r-time">07:00 ✓✓</span>
          </div>
        </div>
        <div class="chat-caption">EXEMPLE DE RAPPORT — LES VRAIS CHIFFRES SERONT CEUX DE TON ENTREPRISE.</div>
      </div>
    </div>
  </div>
</section>

<section id="oxyne" class="env-oxyne">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">05 — ENVIRONNEMENT N°2 · PREMIUM</div>
      <h2>Oxyne Z, dans <em>son</em> environnement : la veille.</h2>
      <p>Son monde n'est pas la conversation, c'est la surveillance. Il regarde ce qui se copie et ce qui s'usurpe sur les réseaux, et il protège le nom de l'entreprise.</p>
    </div>
    <div class="soc-panel rv">
      <div class="radar" aria-hidden="true"></div>
      <div class="soc-status"><span class="dot"></span> VEILLE ACTIVE — SALON « CHEZ AÏCHA »</div>
      <div class="alert">
        <h5>⚠ Fausse page détectée — Facebook</h5>
        <p>Une page « Chez Aïcha — Officiel » copie le nom, le logo et les photos du salon. Elle propose des « promos » et demande des paiements sur un autre numéro.</p>
        <div class="acts">
          <span class="chip done">✓ Preuves capturées</span>
          <span class="chip done">✓ Propriétaire alertée</span>
        </div>
        <div class="pending">
          <div class="p-txt"><span>Signalement prêt</span> — en attente de ton accord</div>
          <button type="button" class="btn-mini go">Envoyer</button>
          <button type="button" class="btn-mini no">Ignorer</button>
        </div>
      </div>
      <div class="alert red">
        <h5>⚠ Numéro suspect détecté</h5>
        <p>Un numéro inconnu réclame des acomptes aux clients au nom du salon. Le seul numéro officiel du salon est enregistré chez Oxyne Z — tout autre numéro est un faux.</p>
        <div class="acts">
          <span class="chip"><b>Dossier de preuves</b> — prêt pour la plainte</span>
          <span class="chip"><b>Alerte publique</b> — texte prêt à publier</span>
        </div>
        <div class="pending">
          <div class="p-txt"><span>Signalement WhatsApp prêt</span> — en attente de ton accord</div>
          <button type="button" class="btn-mini go">Envoyer</button>
          <button type="button" class="btn-mini no">Ignorer</button>
        </div>
      </div>
      <div class="alert ok">
        <h5 style="color:#B7D09A">✓ Vérification client — 10 secondes</h5>
        <p>Un client doute d'un numéro ? Il demande à Aelyra : « c'est bien votre numéro ? » Elle compare au registre officiel d'Oxyne Z et répond oui ou non, net.</p>
      </div>
    </div>
    <div class="soc-rules">
      <div class="soc-rule rv"><h4><span>Il voit.</span></h4><p>Pages, comptes et numéros qui imitent l'entreprise sur Facebook, Instagram et WhatsApp — il les cherche et les suit, pour que tu n'aies pas à le faire.</p></div>
      <div class="soc-rule rv rv-d1"><h4><span>Il prouve.</span></h4><p>Captures, liens, dates : chaque alerte arrive avec un dossier propre. Jamais une rumeur, toujours une preuve.</p></div>
      <div class="soc-rule rv rv-d2"><h4><span>Il fait valider.</span></h4><p>Oxyne Z ne frappe jamais seul : le signalement et l'alerte publique ne partent qu'avec ton accord. La décision reste humaine — la tienne.</p></div>
    </div>
  </div>
</section>

<section id="environnements">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">06 — LES ENVIRONNEMENTS DES COMMERÇANTS</div>
      <h2>Chaque commerçant a <em>son</em> environnement.</h2>
      <p>Quand un salon, une agence et une boutique utilisent la même équipe, rien ne se croise : chacun a son catalogue, ses clients, sa mémoire, son numéro officiel. Comme toi qui causes avec plusieurs personnes — chacun dans son monde, aucune conversation ne fuit dans l'autre.</p>
    </div>
    <div class="merchants">
      <article class="merchant m1 rv">
        <div class="m-head"><div class="m-ico">✂️</div><div><h4>Salon « Chez Aïcha »</h4><div class="m-type">ENVIRONNEMENT — COIFFURE</div></div></div>
        <div class="m-env">
          <div><b>Son catalogue :</b> tresses, tissages, soins — ses prix à elle</div>
          <div><b>Ses clientes :</b> leurs rendez-vous, leurs habitudes</div>
          <div><b>Son numéro officiel :</b> enregistré chez Oxyne Z</div>
        </div>
        <div class="m-chat"><div class="q">« Vous faites les tresses pour enfants ? »</div><div class="a">Aelyra répond avec les tarifs enfants <b>du salon</b> — pas ceux du voisin.</div></div>
      </article>
      <article class="merchant m2 rv rv-d1">
        <div class="m-head"><div class="m-ico">🏠</div><div><h4>Agence Horizon Immo</h4><div class="m-type">ENVIRONNEMENT — IMMOBILIER</div></div></div>
        <div class="m-env">
          <div><b>Son catalogue :</b> ses annonces, ses quartiers, ses loyers</div>
          <div><b>Ses clients :</b> leurs budgets, leurs recherches</div>
          <div><b>Son numéro officiel :</b> enregistré chez Oxyne Z</div>
        </div>
        <div class="m-chat"><div class="q">« Vous avez un studio meublé ? »</div><div class="a">Aelyra propose les biens <b>de l'agence</b> et prend le rendez-vous de visite.</div></div>
      </article>
      <article class="merchant m3 rv rv-d2">
        <div class="m-head"><div class="m-ico">🛍️</div><div><h4>Boutique « Ndèye Fashion »</h4><div class="m-type">ENVIRONNEMENT — COMMERCE</div></div></div>
        <div class="m-env">
          <div><b>Son catalogue :</b> ses articles, ses tailles, son stock</div>
          <div><b>Ses clientes :</b> leurs commandes, leurs livraisons</div>
          <div><b>Son numéro officiel :</b> enregistré chez Oxyne Z</div>
        </div>
        <div class="m-chat"><div class="q">« La robe rouge est encore disponible ? »</div><div class="a">Aelyra vérifie <b>le stock de la boutique</b> et envoie le lien de paiement.</div></div>
      </article>
    </div>
    <div class="walls rv">
      <span class="lock">🔒 CLOISONNEMENT TOTAL</span>
      <span>Les conversations d'un commerçant ne servent jamais un autre — sa mémoire est à lui, ses clientes sont à lui.</span>
    </div>
  </div>
</section>

<section id="tarifs" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">07 — TARIFS</div>
      <h2>Une seule grille, <em>la même partout.</em></h2>
      <p>Trois offres, décrites par ce qu'elles font — pas par des promesses. Tu commences par l'essai, tu choisis ensuite, sans engagement.</p>
    </div>
    <div class="trial-banner rv">
      <div>
        <h3>L'essai d'abord : <em>7 jours gratuits, sans carte.</em></h3>
        <p>On connecte ton WhatsApp, Aelyra répond à tes vrais clients pendant une semaine. Tu juges sur le travail réel, pas sur un discours.</p>
      </div>
      <a class="btn btn-terra" href="#faq">Commencer l'essai gratuit</a>
    </div>
    <div class="plans">
      <article class="plan rv">
        <div class="p-tag">L'ESSENTIEL</div>
        <h4>Essentiel</h4>
        <p class="p-sub">Aelyra répond à tes clients, tous les jours.</p>
        <ul>
          <li>Aelyra sur ton WhatsApp d'entreprise</li>
          <li>Tri par priorité — personne ignoré</li>
          <li>Rendez-vous et rappels</li>
          <li>Ton catalogue, tes prix, tes horaires</li>
          <li>Liens de paiement Orange Money, Wave, MTN</li>
        </ul>
        <div class="price"><span class="amount">6 000 F</span><span class="when">par mois, sans engagement</span></div>
        <a class="btn btn-terra plan-cta" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>?offre=essentiel">Choisir Essentiel</a>
      </article>
      <article class="plan rv rv-d1">
        <div class="p-tag">POUR ALLER PLUS LOIN</div>
        <h4>Pro</h4>
        <p class="p-sub">Essentiel, plus tes réseaux sociaux.</p>
        <ul>
          <li>Tout Essentiel</li>
          <li>Messages Messenger et Instagram</li>
          <li>Publication auto de tes produits (Facebook, Instagram)</li>
          <li>Le rapport du matin, sur ton WhatsApp</li>
          <li>Relances : rendez-vous à confirmer, clients sans réponse</li>
        </ul>
        <div class="price"><span class="amount">10 000 F</span><span class="when">par mois, sans engagement</span></div>
        <a class="btn btn-terra plan-cta" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>?offre=pro">Choisir Pro</a>
      </article>
      <article class="plan featured rv rv-d2">
        <div class="premium-chip2">PREMIUM</div>
        <div class="p-tag">AVEC LA VOIX ET LA VEILLE OXYNE Z</div>
        <h4>Premium</h4>
        <p class="p-sub">Pro, plus les appels et la protection de ton nom.</p>
        <ul>
          <li>Tout Pro</li>
          <li>Aelyra répond aussi à tes appels</li>
          <li>Oxyne Z : détection des fausses pages et comptes</li>
          <li>Alertes avec dossier de preuves</li>
          <li>Signalements préparés — tu valides avant l'envoi</li>
          <li>Vérification de ton numéro officiel par tes clients</li>
        </ul>
        <div class="price"><span class="amount">60 000 F</span><span class="when">par mois, sans engagement</span></div>
        <a class="btn btn-terra plan-cta" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>?offre=premium">Choisir Premium</a>
      </article>
    </div>
    <p class="plans-note rv">Un doute entre deux offres ? Commence par l'essai gratuit : une semaine de travail réel vaut mieux qu'un tableau.</p>
  </div>
</section>

<section id="confiance" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">08 — CONFIANCE ET DONNÉES</div>
      <h2>Tes données sont <em>à toi.</em> Point.</h2>
      <p>Aelyra travaille avec les conversations de tes clients : c'est une confiance, pas un détail. Voici nos règles, écrites noir sur blanc — et les partenaires techniques que nous nommons au lieu de les cacher.</p>
    </div>
    <div class="trust-grid">
      <article class="trust-card rv">
        <div class="t-ico">🔒</div>
        <h4>Cloisonnées par commerçant</h4>
        <p>Chaque entreprise vit dans son environnement. Tes conversations, ton catalogue et tes clients ne sont ni mélangés, ni visibles par un autre commerçant.</p>
      </article>
      <article class="trust-card rv rv-d1">
        <div class="t-ico">🚫</div>
        <h4>Jamais revendues</h4>
        <p>Tes données et celles de tes clients ne sont ni vendues, ni cédées à des fins publicitaires. Elles servent un seul travail : le tien.</p>
      </article>
      <article class="trust-card rv rv-d2">
        <div class="t-ico">🧠</div>
        <h4>Pas d'entraînement public</h4>
        <p>Les conversations de ton entreprise ne servent pas à entraîner des modèles d'IA publics. Ce que tes clients écrivent chez toi reste chez toi.</p>
      </article>
      <article class="trust-card rv">
        <div class="t-ico">🗑️</div>
        <h4>Suppression sur demande</h4>
        <p>Tu pars, ou tu demandes simplement : les données de ton entreprise sont supprimées. Sans rétention cachée, sans condition.</p>
      </article>
      <article class="trust-card rv rv-d1">
        <div class="t-ico">🔐</div>
        <h4>Connexion sécurisée</h4>
        <p>Les échanges avec la plateforme passent par une connexion chiffrée (HTTPS). Ce qui est vrai est dit tel quel — pas de formule magique, pas de promesse en l'air.</p>
      </article>
      <article class="trust-card rv rv-d2">
        <div class="t-ico">🤝</div>
        <h4>Des partenaires nommés</h4>
        <p>La plateforme s'appuie sur des services techniques que nous nommons : Meta pour WhatsApp, notre fournisseur d'intelligence artificielle, et les services de paiement Mobile Money. La transparence fait partie du produit.</p>
      </article>
    </div>
  </div>
</section>

<section id="faq" style="padding-top:20px">
  <div class="wrap">
    <div class="sec-head rv">
      <div class="sec-num">09 — QUESTIONS FRÉQUENTES</div>
      <h2>Ce que les commerçants <em>demandent d'abord.</em></h2>
    </div>
    <div class="faq-list rv">
      <details class="faq-item">
        <summary>Le client sait-il qu'il parle à une intelligence artificielle ?</summary>
        <div class="faq-a">Oui, dès le premier message. Aelyra se présente : « Je suis Aelyra, la réceptionniste des entreprises — je suis une IA. » C'est une règle de la maison : pas de faux semblant, avec personne.</div>
      </details>
      <details class="faq-item">
        <summary>Et si Aelyra ne sait pas répondre ?</summary>
        <div class="faq-a">Elle ne devine pas et elle n'invente ni prix ni prestation. Elle transmet la conversation au patron, avec un résumé clair — et le client est prévenu qu'un humain prend le relais. Le patron retrouve la demande dans son rapport.</div>
      </details>
      <details class="faq-item">
        <summary>Où vont mes données, et celles de mes clients ?</summary>
        <div class="faq-a">Dans ton environnement, cloisonné : elles ne servent aucun autre commerçant, ne sont jamais revendues et n'entraînent pas de modèles publics. Sur ta demande, elles sont supprimées. Le détail est dans la section « Confiance et données » ci-dessus.</div>
      </details>
      <details class="faq-item">
        <summary>Oxyne Z fait quoi, exactement ?</summary>
        <div class="faq-a">Il surveille les fausses pages, les faux comptes et les numéros qui se font passer pour ton entreprise sur Facebook, Instagram et WhatsApp. Quand il détecte quelque chose, il te prévient avec un dossier de preuves et prépare le signalement — qui ne part qu'avec ton accord. Il voit, il prouve, il fait valider.</div>
      </details>
    </div>
  </div>
</section>



<div class="mobile-bar">
  <div class="mb-txt">Essai gratuit<span>7 jours, sans carte</span></div>
  <a class="btn btn-terra" href="<?php echo esc_url( home_url( '/inscription/' ) ); ?>">Essayer gratuitement</a>
</div>


<?php
get_footer();
