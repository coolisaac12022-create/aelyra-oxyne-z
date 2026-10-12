/* AELYRA x OXYNE Z - interactions du site public */
/* Menu burger — la navigation reste utilisable sur mobile */
(function(){
  var burger = document.getElementById('axzBurger');
  var links = document.getElementById('axzNavlinks');
  if (!burger || !links) return;
  burger.addEventListener('click', function(){
    var open = links.classList.toggle('open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    burger.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
  });
  links.addEventListener('click', function(e){
    if (e.target.tagName === 'A') {
      links.classList.remove('open');
      burger.setAttribute('aria-expanded', 'false');
    }
  });
})();

/* Barre de progression */
(function(){
  var prog = document.getElementById('axzProgress');
  if (!prog) return;
  addEventListener('scroll', function(){
    var h = document.documentElement;
    var max = h.scrollHeight - h.clientHeight;
    prog.style.width = (max > 0 ? (h.scrollTop / max) * 100 : 0) + '%';
  }, {passive:true});
})();

/* Révélations au défilement — l'état caché est ajouté par le JS (rv-init),
   jamais par le CSS : si ce script échoue, tout le contenu reste visible. */
(function(){
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var els = document.querySelectorAll('.axz .rv');
  if (reduce || !('IntersectionObserver' in window)) return;
  els.forEach(function(el){ el.classList.add('rv-init'); });
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){
      if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
    });
  }, {threshold: .12});
  els.forEach(function(el){ io.observe(el); });
})();

/* Compteurs du héros */
(function(){
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var els = document.querySelectorAll('.axz [data-count]');
  els.forEach(function(el){
    var target = +el.dataset.count;
    if (reduce || !('IntersectionObserver' in window)) { el.textContent = target; return; }
    var cio = new IntersectionObserver(function(es){
      es.forEach(function(e){
        if (!e.isIntersecting) return;
        var t0 = performance.now();
        (function tick(t){
          var p = Math.min((t - t0) / 1100, 1);
          el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
          if (p < 1) requestAnimationFrame(tick);
        })(t0);
        cio.unobserve(el);
      });
    }, {threshold:.6});
    cio.observe(el);
  });
})();

/* Conversation d'Aelyra — démonstration du tri, en boucle.
   L'animation se met en PAUSE quand le téléphone sort de l'écran. */
(function(){
  var body = document.getElementById('axzChatBody');
  var phone = document.getElementById('axzPhone');
  var typing = document.getElementById('axzTyping');
  if (!body || !typing) return;
  var msgs = Array.prototype.slice.call(body.querySelectorAll('.msg'));
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) { msgs.forEach(function(m){ m.classList.add('show'); }); return; }
  var visible = true;
  if ('IntersectionObserver' in window && phone) {
    new IntersectionObserver(function(es){
      es.forEach(function(e){ visible = e.isIntersecting; });
    }, {threshold:.15}).observe(phone);
  }
  var wait = function(ms){ return new Promise(function(r){ setTimeout(r, ms); }); };
  async function waitVisible(){ while (!visible) { await wait(500); } }
  async function playChat(){
    while (true) {
      msgs.forEach(function(m){ m.classList.remove('show'); });
      typing.classList.remove('on');
      await wait(900);
      for (var i = 0; i < msgs.length; i++) {
        var m = msgs[i];
        await waitVisible();
        if (m.classList.contains('out')) {
          typing.classList.add('on');
          await wait(1050);
          await waitVisible();
          typing.classList.remove('on');
        }
        m.classList.add('show');
        await wait(m.classList.contains('out') ? 1500 : 1250);
      }
      await wait(5200);
    }
  }
  playChat();
})();
