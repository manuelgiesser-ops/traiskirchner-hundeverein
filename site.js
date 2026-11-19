
(()=>{const people=[...document.querySelectorAll('.person>img')];if(!people.length)return;const dlg=document.createElement('dialog');dlg.className='portrait-dialog';dlg.setAttribute('aria-labelledby','portrait-modal-name');dlg.innerHTML='<button type="button" class="portrait-close" aria-label="Foto schließen">×</button><div class="portrait-modal-layout"><div class="portrait-viewport"><img class="portrait-modal-image" alt=""></div><div class="portrait-modal-copy"><h2 id="portrait-modal-name"></h2><p></p></div></div>';document.body.append(dlg);let trigger=null;const close=()=>dlg.close();dlg.querySelector('.portrait-close').addEventListener('click',close);dlg.addEventListener('click',e=>{if(e.target===dlg)close()});dlg.addEventListener('close',()=>{trigger?.focus({preventScroll:true});trigger=null});people.forEach(img=>{const person=img.closest('.person'),name=person.querySelector('h3')?.textContent||img.alt,role=person.querySelector('p')?.textContent||'';const button=document.createElement('button');button.type='button';button.className='portrait-frame';button.setAttribute('aria-label','Trainerfoto von '+name+' in großer Ansicht öffnen');button.setAttribute('aria-haspopup','dialog');img.before(button);button.append(img);const mark=document.createElement('span');mark.className='portrait-icon';mark.setAttribute('aria-hidden','true');mark.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/></svg>';button.append(mark);button.addEventListener('click',()=>{const target=dlg.querySelector('img');target.src=img.dataset.full||img.currentSrc||img.src;target.alt=img.alt;dlg.querySelector('h2').textContent=name;dlg.querySelector('p').textContent=role;trigger=button;if(typeof dlg.showModal==='function')dlg.showModal();else window.open(img.src,'_blank','noopener')})})})();
(()=>{const targets=document.querySelectorAll('.photo>img,.course>img,.gallery-item>img,.bond-image>img');targets.forEach((img,i)=>{const frame=document.createElement('span');frame.className='media-frame';if(i%3===1)frame.dataset.direction='right';img.before(frame);frame.append(img)});const nav=document.querySelector('#nav');const here=location.pathname.split('/').pop()||'index.html';nav?.querySelectorAll('a').forEach(a=>{if(a.getAttribute('href')===here)a.setAttribute('aria-current','page')})})();
(()=>{const reduced=matchMedia('(prefers-reduced-motion: reduce)');let paused=reduced.matches;const root=document.documentElement;const motion=document.getElementById('motion');let interval;const slides=[...document.querySelectorAll('.hero-scene')];let index=0;function setSlide(i){slides.forEach((s,j)=>{s.classList.toggle('active',i===j);s.setAttribute('aria-hidden',String(i!==j))});const c=document.querySelector('.hero-current'),l=document.querySelector('.hero-label');if(c)c.textContent=String(i+1).padStart(2,'0');if(l)l.textContent=slides[i]?.dataset.label||''}function sync(){root.classList.toggle('paused',paused);if(motion)motion.textContent=paused?'Animationen aktivieren':'Animationen pausieren';clearInterval(interval);if(!paused&&slides.length>1)interval=setInterval(()=>{if(document.hidden)return;index=(index+1)%slides.length;setSlide(index)},6500)}if(!paused&&'IntersectionObserver'in window){const obs=new IntersectionObserver(entries=>entries.forEach(x=>{if(x.isIntersecting){x.target.classList.add('seen');obs.unobserve(x.target)}}),{threshold:.06,rootMargin:'0px 0px -3% 0px'});document.querySelectorAll('.photo,.gallery-item,.lead-img,.steps article').forEach(x=>x.classList.add('reveal'));document.querySelectorAll('.courses,.people,.gallery,.steps').forEach(g=>[...g.children].forEach((el,i)=>el.style.setProperty('--stagger',Math.min(i%4,3)*65+'ms')));root.classList.add('motion-ready');document.querySelectorAll('.reveal').forEach(x=>obs.observe(x))}sync();motion?.addEventListener('click',()=>{paused=!paused;sync()});reduced.addEventListener?.('change',e=>{paused=e.matches;sync()});const menu=document.querySelector('.menu'),nav=document.querySelector('#nav');function close(){nav?.classList.remove('open');menu?.setAttribute('aria-expanded','false');menu?.setAttribute('aria-label','Menü öffnen')}menu?.addEventListener('click',()=>{const open=nav.classList.toggle('open');menu.setAttribute('aria-expanded',String(open));menu.setAttribute('aria-label',open?'Menü schließen':'Menü öffnen')});nav?.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));document.addEventListener('click',e=>{if(nav?.classList.contains('open')&&!nav.contains(e.target)&&!menu?.contains(e.target))close()});document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});const dlg=document.querySelector('#lightbox');if(dlg){document.querySelectorAll('.gallery-item').forEach(b=>b.addEventListener('click',()=>{const p=b.querySelector('img');dlg.querySelector('img').src=p.dataset.full||b.dataset.full||p.currentSrc||p.src;dlg.querySelector('img').alt=p.alt;dlg.querySelector('p').textContent=p.alt;dlg.showModal()}));dlg.querySelector('.close').addEventListener('click',()=>dlg.close());dlg.addEventListener('click',e=>{if(e.target===dlg)dlg.close()})}const form=document.querySelector('#contact');form?.addEventListener('submit',e=>{e.preventDefault();const d=new FormData(form);const body=`Name: ${d.get('name')}\nE-Mail: ${d.get('email')}\n\n${d.get('message')}`;const link='mailto:office@traiskirchner-hundeverein.at?subject='+encodeURIComponent('Anfrage an den THV')+'&body='+encodeURIComponent(body);const f=document.querySelector('#mail-fallback');f.href=link;f.hidden=false;document.querySelector('#form-status').textContent='Deine Nachricht ist vorbereitet. Bitte sende sie in deiner E-Mail-App ab.';location.href=link});})();

(()=>{const header=document.querySelector('header'),progress=document.querySelector('.reading-progress');let scheduled=false;function update(){scheduled=false;const y=window.scrollY||0;header?.classList.toggle('scrolled',y>24);if(progress){const avail=document.documentElement.scrollHeight-innerHeight;progress.style.transform='scaleX('+Math.max(0,Math.min(1,avail>0?y/avail:0))+')'}}window.addEventListener('scroll',()=>{if(!scheduled){scheduled=true;requestAnimationFrame(update)}},{passive:true});update();})();

if('IntersectionObserver' in window){const observer=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in-view');observer.unobserve(e.target)}}),{threshold:.12});document.querySelectorAll('.bond-story').forEach(e=>observer.observe(e));}

(()=>{const prefers=matchMedia('(prefers-reduced-motion: reduce)'),desktop=matchMedia('(min-width: 761px) and (pointer: fine)');const frames=[...document.querySelectorAll('.editorial-intro .media-frame,.bond-image .media-frame,.field-editorial .media-frame')];let active=new Set(),pending=false;function draw(){pending=false;const enabled=desktop.matches&&!prefers.matches&&!document.documentElement.classList.contains('paused');for(const e of frames){if(!enabled||!active.has(e)){e.style.removeProperty('--media-y');e.style.removeProperty('--media-scale');continue}const r=e.getBoundingClientRect(),offset=Math.max(-7,Math.min(7,(innerHeight/2-r.top-r.height/2)*.017));e.style.setProperty('--media-scale','1.035');e.style.setProperty('--media-y',offset.toFixed(1)+'px')}}function schedule(){if(!pending){pending=true;requestAnimationFrame(draw)}}if('IntersectionObserver'in window){const obs=new IntersectionObserver(es=>{es.forEach(e=>e.isIntersecting?active.add(e.target):active.delete(e.target));schedule()},{rootMargin:'100px'});frames.forEach(e=>obs.observe(e));window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});prefers.addEventListener?.('change',schedule);document.querySelector('#motion')?.addEventListener('click',schedule)}})();

(()=>{const motion=matchMedia('(prefers-reduced-motion: reduce)'),fine=matchMedia('(hover:hover) and (pointer:fine)');const root=document.documentElement;const hero=document.querySelector('.hero-character');const items=[...document.querySelectorAll('.decorated-section')];if('IntersectionObserver'in window){const o=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in-view');o.unobserve(e.target)}}),{threshold:.06});items.forEach(x=>o.observe(x))}else items.forEach(x=>x.classList.add('in-view'));let ticking=false;const enabled=()=>!motion.matches&&!root.classList.contains('paused');function scroll(){ticking=false;if(!hero)return;const y=enabled()&&fine.matches?Math.max(0,Math.min(32,-hero.getBoundingClientRect().top*.065)):0;hero.style.setProperty('--world-y',y+'px');hero.style.setProperty('--subject-y',(-y*.55)+'px')}function schedule(){if(!ticking){ticking=true;requestAnimationFrame(scroll)}}window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});hero?.addEventListener('pointermove',e=>{if(!enabled()||!fine.matches)return;const r=hero.getBoundingClientRect();hero.style.setProperty('--subject-x',Math.max(-10,Math.min(10,(e.clientX-r.left-r.width/2)*.017))+'px')});hero?.addEventListener('pointerleave',()=>hero.style.setProperty('--subject-x','0px'));document.querySelectorAll('.hero-copy .btn,.bond-copy .btn').forEach(b=>{b.classList.add('magnetic-btn');b.addEventListener('pointermove',e=>{if(!enabled()||!fine.matches)return;const r=b.getBoundingClientRect();b.style.setProperty('--mag-x',((e.clientX-r.left-r.width/2)*.09).toFixed(1)+'px');b.style.setProperty('--mag-y',((e.clientY-r.top-r.height/2)*.12).toFixed(1)+'px')});b.addEventListener('pointerleave',()=>{b.style.setProperty('--mag-x','0px');b.style.setProperty('--mag-y','0px')})});motion.addEventListener?.('change',schedule);document.querySelector('#motion')?.addEventListener('click',schedule);schedule()})();

(()=>{const hero=document.querySelector('.hero-character');if(hero&&'IntersectionObserver'in window){const obs=new IntersectionObserver(es=>es.forEach(e=>hero.classList.toggle('hero-offscreen',!e.isIntersecting)),{threshold:0});obs.observe(hero)}})();


/* THV editorial details: quiet line work, organic reveals and an animated bond seal. */
(()=>{
 const root=document.documentElement,ns='http://www.w3.org/2000/svg',reduced=matchMedia('(prefers-reduced-motion: reduce)');
 const paw='<g fill="currentColor"><ellipse cx="37" cy="35" rx="6.5" ry="10" transform="rotate(-24 37 35)"/><ellipse cx="51" cy="28" rx="6.5" ry="10"/><ellipse cx="65" cy="35" rx="6.5" ry="10" transform="rotate(22 65 35)"/><ellipse cx="77" cy="46" rx="5.8" ry="8.5" transform="rotate(38 77 46)"/><path d="M37 62C38 55 45 52 49 47C53 42 60 43 64 48C68 54 75 59 73 66C71 75 63 73 55 70C48 73 34 74 37 62Z" transform="translate(-4 0)"/></g>';
 const corner='<path class="corner-thread" pathLength="1" d="M-14 156C34 158 54 102 97 111S146 145 182 109S211 42 265 35"/><path class="corner-thread fine" pathLength="1" d="M-20 169C38 171 58 115 99 125S151 156 188 119S213 54 267 48"/><g class="corner-paw" transform="translate(162 55) rotate(-24 55 50) scale(.43)">'+paw+'</g><g class="corner-paw second" transform="translate(207 4) rotate(12 55 50) scale(.34)">'+paw+'</g><path class="corner-grass" d="M8 190Q12 148 1 133M23 190Q11 143 29 117M33 190Q38 147 57 138M47 190Q52 162 68 154"/>';
 const head='<path class="dog-wash" d="M25 183C53 156 69 142 78 119C86 103 81 86 90 72C99 55 116 46 139 45C152 33 167 24 183 27C187 40 185 53 180 66C192 78 199 85 212 89L232 94Q239 98 234 105Q227 117 211 119L194 121Q183 135 163 138Q149 149 144 166L162 183Z"/><path class="corner-thread" pathLength="1" d="M25 183C53 156 69 142 78 119C86 103 81 86 90 72C99 55 116 46 139 45C152 33 167 24 183 27C187 40 185 53 180 66C192 78 199 85 212 89L232 94Q239 98 234 105Q227 117 211 119L194 121Q183 135 163 138Q149 149 144 166Q143 177 162 183"/><path class="corner-thread fine" pathLength="1" d="M139 45Q126 58 124 80Q124 100 139 106Q150 105 158 89L170 61M174 82Q181 78 187 85M214 99q8 4 16 1M116 127Q131 137 155 139M102 146Q128 158 150 154"/><circle cx="182" cy="85" r="2.2" fill="currentColor"/>';
 const running='<path class="dog-wash" d="M23 84C39 94 58 98 78 91C99 82 117 74 145 81C157 84 170 83 182 70L199 48L198 29Q211 24 221 47L234 52L249 64Q256 70 244 75L225 77Q214 96 200 106L221 126L245 136L244 143L224 144L184 126L166 113Q143 124 122 117L99 143L73 153L61 150L68 143L88 134L105 111L83 110L48 123L24 116Q16 112 22 108L45 111L59 103C36 102 20 94 12 80Z"/><path class="corner-thread" pathLength="1" d="M12 80C24 93 44 101 67 94C93 86 117 74 145 81C157 84 170 83 182 70L199 48L198 29Q211 24 221 47L234 52L249 64Q256 70 244 75L225 77Q214 96 200 106L221 126L245 136L244 143L224 144L184 126L166 113Q143 124 122 117L99 143L73 153L61 150L68 143L88 134L105 111L83 110L48 123L24 116Q16 112 22 108L45 111L59 103C36 102 20 94 12 80Z"/><path class="corner-thread fine" pathLength="1" d="M199 48Q195 69 207 75M228 61l4 1M178 88Q186 104 166 113M121 96Q109 106 105 111M83 159h-37m98-11h-29m84 10h-22"/><circle cx="231" cy="61" r="1.7" fill="currentColor"/>';
 const sitting='<path class="dog-wash" d="M26 159Q40 170 65 155Q53 143 63 120Q73 97 98 86L114 69Q109 49 120 37Q135 22 157 31L171 22Q184 20 181 41L198 50L216 60Q224 65 213 72L194 76Q185 94 165 99L162 133L172 158L190 163L188 171H142L138 146Q124 162 103 164L116 171H77Q41 187 23 170Z"/><path class="corner-thread" pathLength="1" d="M26 159Q40 170 65 155Q53 143 63 120Q73 97 98 86L114 69Q109 49 120 37Q135 22 157 31L171 22Q184 20 181 41L198 50L216 60Q224 65 213 72L194 76Q185 94 165 99L162 133L172 158L190 163L188 171H142L138 146Q124 162 103 164L116 171H77Q41 187 23 170Z"/><path class="corner-thread fine" pathLength="1" d="M157 31Q143 40 142 61Q142 78 154 77Q164 71 169 48M165 99Q146 99 135 89M104 164Q118 151 114 138Q110 122 92 122M191 56l4 1M149 158l-1-35"/><circle cx="193" cy="56" r="1.8" fill="currentColor"/>';
 const leaf='<path class="corner-thread" pathLength="1" d="M13 185C34 140 53 97 100 66S180 43 248 12"/><path class="corner-grass" d="M45 131Q9 95 24 73Q53 86 45 131M77 89Q65 46 94 34Q113 60 77 89M128 52Q139 15 173 18Q164 43 128 52M49 126Q91 115 101 139Q70 153 49 126M102 67Q135 62 149 83Q118 98 102 67"/>';

 // Decorative masks are traced from the generated artworks, not generic icons.
 function breed(host,side,kind){if(!host)return;host.classList.add('thv-ornament-host');const node=document.createElement('span');node.className='thv-corner thv-breed '+side+' breed-'+kind;node.setAttribute('aria-hidden','true');node.style.setProperty('--breed-mask','url("generated-media/decor/'+kind+'.svg")');host.prepend(node)}
 function lineDecor(host,side,kind){if(!host)return;host.classList.add('thv-ornament-host');const svg=document.createElementNS(ns,'svg');svg.setAttribute('viewBox','0 0 260 190');svg.setAttribute('aria-hidden','true');svg.setAttribute('focusable','false');svg.setAttribute('class','thv-corner '+side+' '+kind);svg.innerHTML=kind==='leaf'?leaf:corner;host.prepend(svg)}
 const home=document.body.classList.contains('home-page');
 if(home){
  breed(document.querySelector('.editorial-intro'),'corner-right','retriever');
  breed(document.querySelector('.course-editorial'),'corner-right','collie');
  breed(document.querySelector('.team-editorial'),'corner-right','shepherd');
  breed(document.querySelector('.field-editorial'),'corner-left','husky');
  breed(document.querySelector('.final-contact'),'corner-right','retriever');
  lineDecor(document.querySelector('.final-contact'),'corner-left','leaf');
 }else{
  const name=location.pathname.split('/').pop()||'';
  const choices={'agility.html':'agility','hoopers.html':'collie','welpen.html':'retriever','grundkurs.html':'bulldog','fortgeschritten.html':'shepherd','unterordnung.html':'shepherd','social-walk.html':'husky','training.html':'collie','verein.html':'husky','team.html':'retriever','mitgliedschaft.html':'bulldog','kurszeiten.html':'shepherd','galerie.html':'husky','kontakt.html':'retriever','aktuelles.html':'collie'};
  const chosen=choices[name];const lead=document.querySelector('.pagelead');
  if(chosen&&lead&&!lead.classList.contains('withpic'))breed(lead,'corner-right',chosen);else if(lead&&!chosen)lineDecor(lead,'corner-right','trail');
  const sections=[...document.querySelectorAll('main>.section')];
  if(chosen&&lead?.classList.contains('withpic')&&sections.length)breed(sections[sections.length-1],'corner-right',chosen);
 }

 const natureArt="<g class=\"botanic-stems\" stroke=\"currentColor\" stroke-width=\"1.35\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><path d=\"M24 181Q42 139 35 95Q24 69 33 49M35 113Q15 100 10 83Q30 84 36 103M36 92Q54 80 61 65Q44 65 36 82M74 184Q72 133 96 71L105 42M86 103Q64 95 64 78Q83 81 90 90M85 118Q105 104 118 87Q97 86 90 101M136 182Q125 150 142 112Q152 92 149 69M142 123Q119 116 113 101Q135 103 145 113M149 96Q166 88 175 70Q155 74 149 87M191 182Q177 149 193 126Q203 111 206 93M193 125Q176 122 172 110Q186 108 197 115\"/><path d=\"M8 184Q2 158 9 139M55 184Q47 148 59 127M115 184Q110 150 121 138M165 183Q158 156 172 144M221 184Q209 151 229 131M234 185Q243 162 249 151M48 181Q64 153 58 141M151 183Q172 149 162 137\"/><path d=\"M7 184Q73 174 125 185T253 181\" opacity=\".42\"/></g><g fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.15\"><g transform=\"translate(33 46)\"><ellipse cy=\"-7\" rx=\"3.8\" ry=\"6.5\"/><ellipse cy=\"-7\" rx=\"3.8\" ry=\"6.5\" transform=\"rotate(72)\"/><ellipse cy=\"-7\" rx=\"3.8\" ry=\"6.5\" transform=\"rotate(144)\"/><ellipse cy=\"-7\" rx=\"3.8\" ry=\"6.5\" transform=\"rotate(216)\"/><ellipse cy=\"-7\" rx=\"3.8\" ry=\"6.5\" transform=\"rotate(288)\"/><circle r=\"3\" fill=\"currentColor\"/></g><g transform=\"translate(105 40)\"><path d=\"M0 1C-17-6-16-20-5-14C-4-26 5-26 6-14C17-20 18-6 0 1Z\"/><path d=\"M0-1V-12\"/></g><g transform=\"translate(149 66) scale(.85)\"><ellipse cy=\"-8\" rx=\"3.5\" ry=\"7\"/><ellipse cy=\"-8\" rx=\"3.5\" ry=\"7\" transform=\"rotate(72)\"/><ellipse cy=\"-8\" rx=\"3.5\" ry=\"7\" transform=\"rotate(144)\"/><ellipse cy=\"-8\" rx=\"3.5\" ry=\"7\" transform=\"rotate(216)\"/><ellipse cy=\"-8\" rx=\"3.5\" ry=\"7\" transform=\"rotate(288)\"/><circle r=\"3\" fill=\"currentColor\"/></g><g transform=\"translate(206 91) scale(.8)\"><path d=\"M0 0C-13-8-13-16-4-11C-2-23 4-20 5-11C14-16 14-8 0 0Z\"/></g></g><g fill=\"currentColor\" opacity=\".6\"><circle cx=\"18\" cy=\"57\" r=\"1.5\"/><circle cx=\"72\" cy=\"51\" r=\"1.2\"/><circle cx=\"128\" cy=\"61\" r=\"1.8\"/><circle cx=\"184\" cy=\"51\" r=\"1.4\"/><circle cx=\"230\" cy=\"104\" r=\"1.5\"/></g>",hurdleArt="<g stroke=\"currentColor\" fill=\"none\" stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\"><path d=\"M23 157H96M46 157V61L60 51V157M160 157H232M183 157V61L197 51V157M60 82H183M60 93H183M60 117H183M60 128H183M46 61H60M183 61H197M46 127L23 157M197 127L232 157\"/><path d=\"M81 82l9 11m19-11 9 11m19-11 9 11m19-11 9 11M81 117l9 11m19-11 9 11m19-11 9 11m19-11 9 11\" stroke-width=\"1.2\"/><path d=\"M9 164C66 157 125 173 250 164\" stroke-width=\".9\" opacity=\".6\"/><path d=\"M76 52C92 15 151 8 171 46\" stroke-dasharray=\"2 8\" stroke-width=\"1.5\"/></g>",slalomArt="<g fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\" stroke-linejoin=\"round\" stroke-linecap=\"round\"><path d=\"M31 152V55M77 161V36M124 149V53M171 159V36M218 149V53\"/><path d=\"M23 152H39M69 161H85M116 149H132M163 159H179M210 149H226\"/><path d=\"M31 76v17m46-36v17m47 0v17m47-34v17m47 0v17\" stroke-width=\"4\"/><path d=\"M9 154C32 190 53 110 77 141S99 188 124 140 148 110 171 145 194 181 233 143\" stroke-dasharray=\"3 6\" stroke-width=\"1.25\"/></g>";
 function ornament(host,kind,side){if(!host)return;host.classList.add('thv-ornament-host');const svg=document.createElementNS(ns,'svg');svg.setAttribute('viewBox','0 0 260 190');svg.setAttribute('aria-hidden','true');svg.setAttribute('focusable','false');svg.setAttribute('class','thv-corner thv-nature '+side+' nature-'+kind);svg.innerHTML=kind==='flowers'?natureArt:kind==='hurdle'?hurdleArt:slalomArt;host.prepend(svg)}
 if(home){ornament(document.querySelector('.editorial-intro'),'flowers','nature-left');ornament(document.querySelector('.course-editorial'),'hurdle','nature-left');ornament(document.querySelector('.field-editorial'),'flowers','nature-right');ornament(document.querySelector('.final-contact'),'flowers','nature-left')}else{const name=location.pathname.split('/').pop();const sections=[...document.querySelectorAll('main>.section')];const last=sections.at(-1);if(['agility.html','hoopers.html','training.html'].includes(name))ornament(last,name==='hoopers.html'?'slalom':'hurdle','nature-left');else if(['verein.html','welpen.html','social-walk.html','kontakt.html','team.html'].includes(name))ornament(last,'flowers','nature-left')}
 const heroWorld=document.querySelector('.hero-world');if(heroWorld){const svg=document.createElementNS(ns,'svg');svg.setAttribute('viewBox','0 0 260 190');svg.setAttribute('aria-hidden','true');svg.setAttribute('class','hero-botanical');svg.innerHTML=natureArt;heroWorld.append(svg)}
 // The old silhouette illustrations are intentionally retired.
 // Keep the original THV club emblem, including the dog.

 // Vary photo openings by context rather than replaying one identical paw path.
 const trails={
  diagonal:[[26,111,-28],[72,91,12],[118,77,-18],[166,51,16],[213,35,-23]],
  reverse:[[211,106,-12],[163,87,-40],[116,72,-6],[67,49,-38],[24,34,-11]],
  pair:[[91,84,-18],[150,65,13]],
  meadow:[[25,112,-20],[78,120,12],[131,109,-14],[184,115,10]]
 };
 const frames=[...document.querySelectorAll('.media-frame')];
 let courseCount=0,otherCount=0;
 frames.forEach(f=>{
  f.classList.add('thv-media','photo-opening');
  const img=f.querySelector('img');
  const kind=f.closest('.course-modern .course')?['diagonal','reverse','pair','diagonal','meadow','reverse','pair'][courseCount++%7]:'soft';
  f.dataset.opening=kind;
  if(kind==='soft')return;
  f.classList.add('paw-reveal');
  const svg=document.createElementNS(ns,'svg');svg.setAttribute('viewBox','0 0 240 140');svg.setAttribute('preserveAspectRatio','xMidYMid meet');svg.setAttribute('aria-hidden','true');svg.setAttribute('focusable','false');svg.setAttribute('class','course-click-trail');
  const gap=f.closest('.course-modern .course')?(kind==='pair'?190:130):(kind==='pair'?630:kind==='meadow'?470:380),scale=kind==='pair'?.42:kind==='meadow'?.27:.32;
  svg.innerHTML=trails[kind].map(([x,y,r],i)=>'<g transform="translate('+x+' '+y+') rotate('+r+') scale('+scale+') translate(-55 -50)"><g class="dog-paw" style="--paw-delay:'+i*gap+'ms">'+paw+'</g></g>').join('');f.append(svg)
 });

 const seal=document.querySelector('.thv-bond-seal'),nodes=[...document.querySelectorAll('.thv-corner,.bond-journey'),...frames,...(seal?[seal]:[])];
 const show=e=>e.classList.add(e.classList.contains('thv-media')?'thv-media-seen':'thv-ornament-seen');
 if(!reduced.matches&&'IntersectionObserver'in window){
  root.classList.add('thv-motion');
  const fine=matchMedia('(hover:hover) and (pointer:fine)');
  const obs=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){show(e.target);obs.unobserve(e.target)}}),{threshold:.06,rootMargin:'0px 0px -3% 0px'});
  const courseFrames=frames.filter(f=>f.closest('.course-modern .course'));
  const courseSet=new Set(courseFrames);
  nodes.filter(n=>!courseSet.has(n)).forEach(n=>obs.observe(n));
  courseFrames.forEach(f=>{
   const card=f.closest('.course');
   f.classList.add('course-discovery');
   let opening=false,timer=null;
   show(f);
   card.addEventListener('click',e=>{
    if(e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey||card.target&&card.target!=='_self'||card.hasAttribute('download'))return;
    if(reduced.matches||root.classList.contains('paused'))return;
    if(eventualUrlIsExternal(card.href))return;
    e.preventDefault();
    if(opening)return;
    opening=true;
    card.classList.add('course-tap-opening');
    card.setAttribute('aria-busy','true');
    timer=window.setTimeout(()=>{window.location.assign(card.href)},950);
   });
   function eventualUrlIsExternal(href){return new URL(href,location.href).origin!==location.origin}
   window.addEventListener('pageshow',e=>{if(e.persisted){clearTimeout(timer);opening=false;card.classList.remove('course-tap-opening');card.removeAttribute('aria-busy')}});


  });
 }else nodes.forEach(show);
})();


/* Generated focal points are measured from the original photos, not guessed per browser. */
const thvPhotoData=Promise.resolve({"faces":{"sari":{"x":0.39743,"y":0.59646,"width":0.09398,"height":0.08264},"michi":{"x":0.45381,"y":0.67327,"width":0.31698,"height":0.22721},"harald":{"x":0.47796,"y":0.63798,"width":0.09919,"height":0.07633},"karo":{"x":0.48014,"y":0.64806,"width":0.09176,"height":0.0783},"otto":{"x":0.58971,"y":0.57122,"width":0.10424,"height":0.08876},"mary":{"x":0.54085,"y":0.65217,"width":0.08818,"height":0.07666},"susi":{"x":0.50403,"y":0.64376,"width":0.07711,"height":0.07285},"alex":{"x":0.54689,"y":0.62043,"width":0.0971,"height":0.08271},"sabrina":{"x":0.49646,"y":0.61626,"width":0.09262,"height":0.07958},"conny":{"x":0.42139,"y":0.62076,"width":0.10588,"height":0.0855},"patrick":{"x":0.50867,"y":0.59547,"width":0.10251,"height":0.09235},"gabi":{"x":0.45399,"y":0.63248,"width":0.08479,"height":0.08129}}});

(()=>{
 const dlg=document.querySelector('.portrait-dialog');if(!dlg)return;
 const image=dlg.querySelector('.portrait-modal-image'),viewport=dlg.querySelector('.portrait-viewport');if(!image||!viewport)return;
 let generation=0,zoom=false,point={x:.5,y:.22},scale=1.65,drag=null,manual=false,current=null,focus=null;
 const enabled=()=>!matchMedia('(prefers-reduced-motion: reduce)').matches&&!document.documentElement.classList.contains('paused');
 const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
 function apply(animate=true){
  const w=image.offsetWidth,h=image.offsetHeight,iw=image.naturalWidth,ih=image.naturalHeight;
  if(!w||!h||!iw||!ih)return;
  const fit=Math.min(w/iw,h/ih),rw=iw*fit,rh=ih*fit;
  // Same gentle magnification for every trainer; never upscale sharply.
  const s=zoom?scale:1, limitX=Math.max(0,(rw*s-w)/2),limitY=Math.max(0,(rh*s-h)/2),x=zoom?clamp((.5-point.x)*rw*s,-limitX,limitX):0,y=zoom?clamp((.5-point.y)*rh*s,-limitY,limitY):0;
  viewport.style.setProperty('--portrait-scale',String(s));viewport.style.setProperty('--portrait-pan-x',x+'px');viewport.style.setProperty('--portrait-pan-y',y+'px');
  viewport.classList.toggle('portrait-no-motion',!animate||!enabled());viewport.classList.toggle('portrait-zoomed',zoom);

 }
 function open(button){
  const id=++generation;current=button;zoom=false;manual=false;focus=null;point={x:.5,y:.22};scale=1.65;
  viewport.style.setProperty('--portrait-scale','1');viewport.style.setProperty('--portrait-pan-x','0px');viewport.style.setProperty('--portrait-pan-y','0px');viewport.classList.remove('portrait-zoomed');viewport.classList.add('portrait-no-motion');
  let triggered=false;
  const valid=()=>id===generation&&dlg.open;
  const use=f=>{if(!valid()||manual||!f||!Number.isFinite(f.x)||!Number.isFinite(f.y))return;focus=f;point={x:clamp(f.x,0,1),y:clamp(f.y,0,1)};if(zoom)apply(true)};
  const start=()=>{if(triggered||!valid()||!image.naturalWidth)return;triggered=true;apply(false);requestAnimationFrame(()=>requestAnimationFrame(()=>{if(!valid())return;zoom=true;apply(true)}))};
  const ready=async()=>{
   if(!valid())return;
   const key=button.querySelector('img')?.dataset.asset;
   const timer=setTimeout(start,700);
   const data=await thvPhotoData;if(!valid())return;const f=data?.faces?.[key];
   if(f){use(f);clearTimeout(timer);start();return}
   try{if('FaceDetector'in window){
    const faces=await new FaceDetector({fastMode:true,maxDetectedFaces:4}).detect(image);
    if(!valid())return;
    const best=faces.map(a=>a.boundingBox).sort((a,b)=>b.width*b.height-a.width*a.height)[0];
    if(best)use({x:(best.x+best.width/2)/image.naturalWidth,y:(best.y+best.height/2)/image.naturalHeight,width:best.width/image.naturalWidth,height:best.height/image.naturalHeight});
   }}catch(_){}finally{clearTimeout(timer);start()}
  };
  if(image.complete&&image.naturalWidth)ready();else image.addEventListener('load',ready,{once:true});
 }
 document.querySelectorAll('.portrait-frame').forEach(b=>b.addEventListener('click',()=>open(b)));

 viewport.addEventListener('pointerdown',e=>{if(!zoom||e.button!==0)return;drag={x:e.clientX,y:e.clientY,px:point.x,py:point.y};manual=true;viewport.setPointerCapture?.(e.pointerId)});
 viewport.addEventListener('pointermove',e=>{if(!drag)return;const w=image.offsetWidth,h=image.offsetHeight,fit=Math.min(w/(image.naturalWidth||w),h/(image.naturalHeight||h));point={x:clamp(drag.px-(e.clientX-drag.x)/Math.max(1,(image.naturalWidth||w)*fit*scale),0,1),y:clamp(drag.py-(e.clientY-drag.y)/Math.max(1,(image.naturalHeight||h)*fit*scale),0,1)};apply(false)});
 for(const event of ['pointerup','pointercancel','lostpointercapture'])viewport.addEventListener(event,()=>{drag=null});
 viewport.addEventListener('dblclick',()=>{if(!current)return;zoom=!zoom;apply(true)});
 window.addEventListener('resize',()=>{if(dlg.open)apply(false)});dlg.addEventListener('close',()=>{generation++;zoom=false;drag=null;current=null;apply(false)});
 image.setAttribute('draggable','false');
})();

/* Match the depth layer to the actual fitted photo, then feather its real edges. */
(()=>{
 const hero=document.querySelector('.hero-subject'),img=hero?.querySelector('img[data-asset="jump"]');if(!img)return;
 const layer=document.createElement('div');layer.className='hero-depth';layer.setAttribute('aria-hidden','true');
 const bg=new Image(),dog=new Image();bg.alt='';dog.alt='';bg.className='hero-depth-background';dog.className='hero-depth-dog';
 function sizeMask(){const w=hero.clientWidth,h=hero.clientHeight,iw=bg.naturalWidth||img.naturalWidth,ih=bg.naturalHeight||img.naturalHeight;if(!w||!h||!iw||!ih)return;const fit=Math.min(w/iw,h/ih),mw=iw*fit+'px',mh=ih*fit+'px';for(const node of [img,hero]){node.style.setProperty('--hero-mask-w',mw);node.style.setProperty('--hero-mask-h',mh)}layer.style.width=mw;layer.style.height=mh}
 img.addEventListener('load',sizeMask);sizeMask();if('ResizeObserver'in window)new ResizeObserver(sizeMask).observe(hero);else window.addEventListener('resize',sizeMask,{passive:true});
 let loaded=0;const ready=()=>{if(++loaded!==2||bg.naturalWidth!==dog.naturalWidth||bg.naturalHeight!==dog.naturalHeight)return;layer.append(bg,dog);hero.append(layer);sizeMask();requestAnimationFrame(()=>hero.classList.add('has-hero-depth'))};
 bg.onload=ready;dog.onload=ready;bg.src='generated-media/hero-depth-background.webp?v=16';dog.src='generated-media/hero-depth-dog.webp?v=16';
})();
/* Group photos remain natural, without magnifier or zoom overlay. Gallery still uses its normal lightbox. */


/* Friendly paw marks on navigation and action links, not functional direction controls. */
(()=>{const paw="<svg viewBox=\"0 0 32 32\" aria-hidden=\"true\" focusable=\"false\" fill=\"currentColor\"><ellipse cx=\"7.1\" cy=\"12.3\" rx=\"3.4\" ry=\"5\" transform=\"rotate(-28 7.1 12.3)\"/><ellipse cx=\"13.2\" cy=\"7.4\" rx=\"3.5\" ry=\"5.2\" transform=\"rotate(-9 13.2 7.4)\"/><ellipse cx=\"21\" cy=\"7.4\" rx=\"3.5\" ry=\"5.2\" transform=\"rotate(9 21 7.4)\"/><ellipse cx=\"27\" cy=\"12.3\" rx=\"3.4\" ry=\"5\" transform=\"rotate(28 27 12.3)\"/><path d=\"M6.5 23.1C6.5 19.4 10.3 17.1 12.6 14.2Q17.1 9.3 21.4 14.2C23.9 17.1 27 19.5 27 23.1C27 29.5 21.1 30 17 27.6C12.5 30 6.5 29.5 6.5 23.1Z\"/></svg>";document.querySelectorAll('a.btn,a.textlink,a.hero-secondary,a.course').forEach(link=>{link.querySelectorAll('span').forEach(s=>{if(!['↗','→'].includes(s.textContent.trim())||s.children.length)return;s.classList.add('link-paw');s.setAttribute('aria-hidden','true');s.innerHTML=paw})})})();

/* V28: compact course return link follows a mobile reader only while needed. */
(()=>{
 const top=document.querySelector('.pagelead .course-back'),bottom=document.querySelector('.course-return .course-back');
 if(!top||!bottom)return;
 const link=top.cloneNode(true);link.className='mobile-course-return';link.textContent='';link.append(top.querySelector('svg').cloneNode(true),document.createTextNode('Alle Kurse'));
 link.setAttribute('aria-label','Zurück zu allen Kursen');link.hidden=true;document.body.append(link);
 const mobile=matchMedia('(max-width:760px)');let pending=false;
 function sync(){pending=false;
  const a=top.getBoundingClientRect(),b=bottom.getBoundingClientRect(),header=document.querySelector('header')?.getBoundingClientRect().bottom||0;
  const visible=mobile.matches&&a.bottom<header+3&&b.top>innerHeight-55&&!document.querySelector('#nav.open');
  link.hidden=!visible;
 }
 function schedule(){if(!pending){pending=true;requestAnimationFrame(sync)}}
 window.addEventListener('scroll',schedule,{passive:true});window.addEventListener('resize',schedule,{passive:true});mobile.addEventListener?.('change',schedule);
 document.querySelector('.menu')?.addEventListener('click',schedule);
 if('MutationObserver'in window){const nav=document.querySelector('#nav');if(nav)new MutationObserver(schedule).observe(nav,{attributes:true,attributeFilter:['class']})}
 sync();
})();

/* V34: accessible, local course finder. Recommendations follow the published prerequisites. */
(()=>{
 const finder=document.querySelector('.course-finder');if(!finder)return;
 const form=finder.querySelector('.finder-form'),result=form.querySelector('.finder-result'),status=form.querySelector('.finder-result-status'),reset=form.querySelector('.finder-reset');
 const step=name=>form.querySelector('[data-finder-step="'+name+'"]');
 const val=name=>form.querySelector('input[name="'+name+'"]:checked')?.value||'';
 const courses={
  puppy:{href:'welpen.html',name:'Welpen und Junghunde',info:'Spielerisch Bindung, Rückruf, Leinenführigkeit und erste Signale aufbauen.'},
  basic:{href:'grundkurs.html',name:'Grundkurs',info:'Alltag, Kommunikation und Grundgehorsam gemeinsam trainieren.'},
  obedience:{href:'unterordnung.html',name:'Unterordnung für Anfänger',info:'Die Grundlagen der Unterordnung kennenlernen und Zusammenarbeit üben.'},
  advanced:{href:'fortgeschritten.html',name:'Unterordnung für Fortgeschrittene',info:'Mit bestandener Begleithundeprüfung erlernte Übungen festigen und verfeinern.'},
  agility:{href:'agility.html',name:'Agility',info:'Gemeinsam einen Hindernisparcours meistern. Geeignet ab einem Jahr für gesunde Hunde.'},
  hoopers:{href:'hoopers.html',name:'Hoopers',info:'Den Hund auf Distanz durch einen Parcours ohne Sprünge führen. Ab einem Jahr.'},
  social:{href:'social-walk.html',name:'Social Walk',info:'Begleitete Spaziergänge zum Üben von Hundebegegnungen, Leinenführigkeit und Impulskontrolle.'}
 };
 function render(keys,note){
  const items=keys.map(key=>{
   const data=courses[key],source=document.querySelector('.course-directory a.course[href="'+data.href+'"] img');
   const article=document.createElement('article');article.className='finder-result-card';
   if(source){const img=source.cloneNode(false);img.removeAttribute('class');img.removeAttribute('data-asset');img.removeAttribute('data-full');img.setAttribute('sizes','(max-width: 760px) 33vw, 180px');img.loading='lazy';img.alt='';article.append(img)}
   const body=document.createElement('div');body.className='finder-result-copy';
   const title=document.createElement('h3');title.textContent=data.name;const p=document.createElement('p');p.textContent=data.info;
   const link=document.createElement('a');link.className='finder-result-link';link.href=data.href;link.textContent='Zum Kurs';const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');svg.setAttribute('viewBox','0 0 24 24');svg.setAttribute('aria-hidden','true');svg.innerHTML='<path d="M5 12h14m-6-6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>';link.append(svg);
   body.append(title,p,link);article.append(body);return article;
  });
  const title=document.createElement('p');title.className='finder-result-label';title.textContent=keys.length===1?'Das könnte gut zu euch passen':'Diese Kurse könnten zu euch passen';
  const grid=document.createElement('div');grid.className='finder-result-grid';grid.append(...items);
  const foot=document.createElement('p');foot.className='finder-result-note';foot.textContent=(note?note+' ':'')+'Die endgültige Einteilung erfolgt durch das Trainerteam.';
  result.replaceChildren(title,grid,foot);result.hidden=false;status.textContent='Empfehlung: '+keys.map(k=>courses[k].name).join(' oder ');
 }
 function update(){
  const age=val('age'),goal=val('goal'),bh=val('bh'),sport=val('sport');
  step('goal').hidden=!age;
  step('goal').querySelector('legend').innerHTML=age==='young'?'<span>2</span> Was möchtet ihr später trainieren? (optional)':'<span>2</span> Was möchtet ihr gemeinsam erreichen?';
  step('bh').hidden=!(age&&age!=='young'&&goal==='obedience');
  step('sport').hidden=!(age==='adult'&&goal==='sport');
  reset.hidden=!age;result.hidden=true;status.textContent='';
  if(!age)return;
  if(age==='young'){
   if(goal==='social')return render(['puppy'],'Hier lernt dein junger Hund neue Situationen kennen. Ein Social Walk kann zusätzlich infrage kommen. Ob die Gruppe und das Alter passen, klärt das Trainerteam.');
   if(goal==='sport')return render(['puppy'],'Jetzt könnt ihr spielerisch Geschicklichkeit und Zusammenarbeit aufbauen. Agility und Hoopers sind ab einem Jahr möglich.');
   if(goal==='obedience')return render(['puppy'],'Hier lernt ihr erste Signale und sichere Zusammenarbeit. Ab neun Monaten könnt ihr in die Unterordnung für Anfänger wechseln.');
   if(goal==='everyday')return render(['puppy'],'Der Kurs legt die Grundlage für Rückruf, Leinenführigkeit und Vertrauen. Ab neun Monaten steht euch der Grundkurs offen.');
   return render(['puppy'],'Für Hunde unter neun Monaten ist dies der altersgerechte Einstieg. Wähle optional euer Trainingsziel, um mehr über die nächsten Schritte zu erfahren.');
  }
  if(!goal)return;
  if(goal==='everyday')return render(['basic'],'');
  if(goal==='social')return render(['social'],'Termine und die passende Gruppe bitte vorab erfragen.');
  if(goal==='obedience'&&bh)return render([bh==='yes'?'advanced':'obedience'],'');
  if(goal==='sport'&&age==='junior')return render(['basic'],'Agility und Hoopers sind ab einem Jahr möglich. Bis dahin könnt ihr Grundlagen festigen.');
  if(goal==='sport'&&sport)return render(sport==='both'?['agility','hoopers']:[sport],'Besprich vorab, welche sportliche Belastung zu deinem Hund passt.');
 }
 form.addEventListener('change',e=>{
  if(e.target.name==='age'){form.querySelectorAll('input[name="goal"],input[name="bh"],input[name="sport"]').forEach(i=>i.checked=false)}
  if(e.target.name==='goal'){form.querySelectorAll('input[name="bh"],input[name="sport"]').forEach(i=>i.checked=false)}
  update();
 });
 form.addEventListener('submit',e=>e.preventDefault());
 form.addEventListener('reset',()=>{requestAnimationFrame(()=>{update();form.querySelector('input[name="age"]')?.focus({preventScroll:true})})});
 form.hidden=false;update();
})();


/* V38: archive photographs open in a dedicated viewer, without affecting group photos. */
(()=>{
 const photos=[...document.querySelectorAll('.archive-photo')];
 if(!photos.length)return;
 const dlg=document.createElement('dialog');
 dlg.className='thv-archive-viewer';
 dlg.setAttribute('aria-labelledby','thv-archive-caption');
 dlg.innerHTML='<header><span class="archive-count" aria-live="polite"></span><div class="archive-controls"><button type="button" data-prev aria-label="Vorheriges Foto">‹</button><button type="button" data-next aria-label="Nächstes Foto">›</button><button type="button" data-dismiss aria-label="Foto schließen">×</button></div></header><img alt=""><p id="thv-archive-caption"></p>';
 document.body.append(dlg);
 const picture=dlg.querySelector('img'),caption=dlg.querySelector('p'),count=dlg.querySelector('.archive-count'),prev=dlg.querySelector('[data-prev]'),next=dlg.querySelector('[data-next]');
 let entries=[],index=0,trigger=null,start=null;
 function show(i){
  if(i<0||i>=entries.length)return;
  index=i;const {img,figure}=entries[i];
  picture.src=img.dataset.full||img.currentSrc||img.src;
  picture.alt=img.alt;
  caption.textContent=figure.querySelector('figcaption')?.textContent||img.alt||'Archivfoto';
  count.textContent=(i+1)+' / '+entries.length;
  prev.disabled=i===0;next.disabled=i===entries.length-1;
  prev.hidden=next.hidden=entries.length<=1;
 }
 const all=photos.map(figure=>{
  const img=figure.querySelector('img');if(!img)return null;
  const button=document.createElement('button');button.type='button';button.className='archive-open';
  button.setAttribute('aria-label','Foto vergrößern: '+(img.alt||'Vereinsarchiv'));button.setAttribute('aria-haspopup','dialog');
  const icon=document.createElement('span');icon.className='archive-expand';icon.setAttribute('aria-hidden','true');
  icon.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/></svg>';
  img.before(button);button.append(img,icon);
  return {img,figure,button};
 }).filter(Boolean);
 all.forEach(entry=>entry.button.addEventListener('click',()=>{
  entries=all.filter(e=>e.figure.closest('.archive-grid')===entry.figure.closest('.archive-grid')&&!e.figure.closest('[hidden]'));
  if(!entries.includes(entry))entries=[entry];
  trigger=entry.button;show(entries.indexOf(entry));
  if(typeof dlg.showModal==='function'){dlg.showModal();dlg.querySelector('[data-dismiss]').focus({preventScroll:true})}
  else window.open(picture.src,'_blank','noopener');
 }));
 prev.addEventListener('click',()=>show(index-1));next.addEventListener('click',()=>show(index+1));
 dlg.querySelector('[data-dismiss]').addEventListener('click',()=>dlg.close());
 dlg.addEventListener('click',e=>{if(e.target===dlg)dlg.close()});
 dlg.addEventListener('keydown',e=>{if(e.key==='ArrowLeft'||e.key==='ArrowRight'){e.preventDefault();show(index+(e.key==='ArrowRight'?1:-1))}});
 dlg.addEventListener('close',()=>{trigger?.focus({preventScroll:true});trigger=null;picture.removeAttribute('src')});
 picture.addEventListener('touchstart',e=>{start=e.touches.length===1?{x:e.touches[0].clientX,y:e.touches[0].clientY}:null},{passive:true});
 picture.addEventListener('touchend',e=>{if(!start)return;const t=e.changedTouches[0],dx=t.clientX-start.x,dy=t.clientY-start.y;start=null;if(Math.abs(dx)>60&&Math.abs(dx)>Math.abs(dy)*1.5)show(index+(dx<0?1:-1))},{passive:true});
})();


/* V41: a friendly little paw wiggle on activation, including touch and keyboard. */
(()=>{
 const reduced=matchMedia('(prefers-reduced-motion: reduce)');
 const pending=new WeakSet(),running=new WeakMap();
 const duration=240;
 document.addEventListener('click',e=>{
  if(e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey||reduced.matches||document.documentElement.classList.contains('paused'))return;
  const link=e.target.closest?.('a.btn,a.textlink,a.hero-secondary');
  const paw=link?.querySelector('.link-paw svg');
  if(!paw||typeof paw.animate!=='function'||link.closest('.course'))return;
  if(pending.has(link)){e.preventDefault();return}
  running.get(paw)?.cancel();
  const effect=paw.animate([
   {transform:'rotate(0deg)',offset:0},
   {transform:'rotate(-16deg)',offset:.18},
   {transform:'rotate(14deg)',offset:.38},
   {transform:'rotate(-10deg)',offset:.58},
   {transform:'rotate(7deg)',offset:.78},
   {transform:'rotate(0deg)',offset:1}
  ],{duration,easing:'ease-out'});
  running.set(paw,effect);
  // Keep modified clicks, new tabs, external URLs, downloads and in-page anchors native.
  if(link.hasAttribute('download')||(link.target&&link.target!=='_self'))return;
  const url=new URL(link.href,location.href);
  if(!/^https?:$/.test(url.protocol)||url.origin!==location.origin)return;
  if(url.pathname===location.pathname&&url.search===location.search&&url.hash)return;
  e.preventDefault();pending.add(link);
  const go=()=>{pending.delete(link);location.assign(url.href)};
  effect.finished.then(go,go);
 });
})();
