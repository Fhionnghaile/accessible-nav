(function(){
"use strict";
var D=document, root=D.documentElement, LS=null;
try{LS=window.localStorage;}catch(e){}
function $(s,c){return (c||D).querySelector(s);}
// config injected by PHP (destinations harvested from the site's own menus/pages)
var CFG={destinations:[],strings:{}};
try{var el=$('#accessible-nav-config');if(el)CFG=JSON.parse(el.textContent)||CFG;}catch(e){}
var STR=CFG.strings||{};
function live(m){var l=$('#an-live'); if(l){l.textContent=''; setTimeout(function(){l.textContent=m;},30);}}
function get(k,d){try{var v=LS&&LS.getItem(k);return v===null||v===undefined?d:v;}catch(e){return d;}}
function set(k,v){try{LS&&LS.setItem(k,v);}catch(e){}}
function esc(s){return String(s).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}

/* ---- landmarks + skip links (attribute-only, no DOM restructure) ---- */
function enhance(){
  var nav=$('nav')||D.getElementById('site-nav');
  if(nav){if(!nav.getAttribute('role'))nav.setAttribute('role','navigation');if(!nav.getAttribute('aria-label'))nav.setAttribute('aria-label','Primary');if(nav.getAttribute('tabindex')===null)nav.setAttribute('tabindex','-1');if(!nav.id)nav.id='an-nav';}
  var main=D.getElementById('main')||$('main');
  if(main){main.setAttribute('tabindex','-1');if(!main.id)main.id='main';}
  var ft=$('footer');if(ft){if(!ft.getAttribute('role'))ft.setAttribute('role','contentinfo');if(!ft.id)ft.id='an-foot';ft.setAttribute('tabindex','-1');}
  var sk=$('#an-skip');
  if(sk){
    var links=[];
    if(nav)links.push(['#'+nav.id,'Skip to navigation']);
    if(main)links.push(['#'+main.id,'Skip to content']);
    if(ft)links.push(['#'+ft.id,'Skip to footer']);
    sk.innerHTML=links.map(function(l){return '<a href="'+l[0]+'">'+l[1]+'</a>';}).join('');
    sk.addEventListener('click',function(e){var a=e.target.closest('a');if(!a)return;var t=D.querySelector(a.getAttribute('href'));if(t){t.setAttribute('tabindex','-1');setTimeout(function(){t.focus();},0);}});
  }
}

/* ---- accessibility toggles ---- */
function applyPrefs(){
  root.setAttribute('data-an-textsize', get('an_ts','normal'));
  root.setAttribute('data-an-contrast', get('an_contrast','0')==='1'?'high':'off');
  root.setAttribute('data-an-motion', get('an_motion','0')==='1'?'reduce':'off');
  root.setAttribute('data-an-font', get('an_font','0')==='1'?'readable':'off');
  root.setAttribute('data-an-links', get('an_links','0')==='1'?'underline':'off');
}
function syncPanel(){
  var ts=get('an_ts','normal');
  Array.prototype.forEach.call(D.querySelectorAll('.an-seg button'),function(b){b.setAttribute('aria-pressed', b.getAttribute('data-ts')===ts?'true':'false');});
  [['contrast','an_contrast'],['motion','an_motion'],['font','an_font'],['links','an_links']].forEach(function(p){
    var b=D.querySelector('.an-tgl[data-tgl="'+p[0]+'"]');if(!b)return;var on=get(p[1],'0')==='1';b.setAttribute('aria-pressed',on?'true':'false');b.textContent=on?'On':'Off';
  });
}
function bindPanel(){
  Array.prototype.forEach.call(D.querySelectorAll('.an-seg button'),function(b){b.addEventListener('click',function(){set('an_ts',b.getAttribute('data-ts'));applyPrefs();syncPanel();live('Text size '+b.textContent);});});
  Array.prototype.forEach.call(D.querySelectorAll('.an-tgl'),function(b){b.addEventListener('click',function(){var map={contrast:'an_contrast',motion:'an_motion',font:'an_font',links:'an_links'};var k=map[b.getAttribute('data-tgl')];var on=get(k,'0')==='1';set(k,on?'0':'1');applyPrefs();syncPanel();var nm=b.parentNode&&b.parentNode.querySelector('span')?b.parentNode.querySelector('span').textContent:'Setting';live(nm+' '+(on?'off':'on'));});});
  $('#an-reset').addEventListener('click',function(){['an_ts','an_contrast','an_motion','an_font','an_links'].forEach(function(k){set(k,k==='an_ts'?'normal':'0');});applyPrefs();syncPanel();live(STR.reset||'Settings reset');});
}

/* ---- panel open/close ---- */
var btn,panel;
function panelOpen(o){panel.setAttribute('data-open',o?'1':'0');btn.setAttribute('aria-expanded',o?'true':'false');if(o){var f=panel.querySelector('button');f&&f.focus();}}
function bindPanelToggle(){
  btn=$('#an-btn');panel=$('#an-panel');
  btn.addEventListener('click',function(){panelOpen(panel.getAttribute('data-open')!=='1');});
  D.addEventListener('click',function(e){if(panel.getAttribute('data-open')==='1'&&!panel.contains(e.target)&&e.target!==btn&&!btn.contains(e.target))panelOpen(false);});
  $('#an-go').addEventListener('click',function(){panelOpen(false);cmdOpen();});
}

/* ---- destinations (from config + harvested nav) ---- */
var DEST=[];
function buildDest(){
  DEST=(CFG.destinations||[]).map(function(d){return {label:d.label,href:d.href,keys:d.keys||''};});
  var seen={};DEST.forEach(function(d){seen[(d.href||'').replace(/\/$/,'')]=1;});
  Array.prototype.forEach.call(D.querySelectorAll('nav a[href], footer a[href]'),function(a){
    var href=a.href,t=(a.textContent||'').trim();
    if(!t||!href||/^(mailto:|tel:|#|javascript:)/i.test(a.getAttribute('href')||''))return;
    var key=href.replace(/\/$/,'');if(seen[key])return;seen[key]=1;
    DEST.push({label:t.replace(/\s+/g,' ').slice(0,60),href:href,keys:''});
  });
}
var ACTIONS=[
 {label:'Toggle: Boost contrast',act:function(){D.querySelector('.an-tgl[data-tgl="contrast"]').click();}},
 {label:'Toggle: Reduce motion',act:function(){D.querySelector('.an-tgl[data-tgl="motion"]').click();}},
 {label:'Toggle: Readable font',act:function(){D.querySelector('.an-tgl[data-tgl="font"]').click();}},
 {label:'Larger text',act:function(){var o={normal:'large',large:'xlarge',xlarge:'xlarge'};set('an_ts',o[get('an_ts','normal')]);applyPrefs();syncPanel();live('Text larger');}}
];

/* ---- command palette ---- */
var cmdk,input,listEl,items=[],sel=0,opener=null;
function rowHTML(it,i,isAct){return '<li id="ank'+i+'" role="option" aria-selected="'+(i===sel?'true':'false')+'" data-i="'+i+'"><span>'+esc(it.label)+'</span>'+(it.keys?'<span class="k">'+esc(it.keys)+'</span>':(isAct?'<span class="k">action</span>':''))+'</li>';}
function render(q){
  q=(q||'').toLowerCase().trim();
  var pool=DEST.map(function(d){return {label:d.label,keys:d.keys,go:function(){location.href=d.href;}};})
    .concat(ACTIONS.map(function(a){return {label:a.label,keys:'',act:a.act,isAct:true};}));
  items=pool.filter(function(it){return !q||it.label.toLowerCase().indexOf(q)>=0;});
  if(sel>=items.length)sel=0;
  listEl.innerHTML=items.map(function(it,i){return rowHTML(it,i,it.isAct);}).join('');
  var a=D.getElementById('ank'+sel);if(a){input.setAttribute('aria-activedescendant','ank'+sel);a.scrollIntoView({block:'nearest'});}
}
function choose(i){var it=items[i];if(!it)return;cmdClose();if(it.act)it.act();else if(it.go)it.go();}
function cmdOpen(){
  if(panel&&panel.getAttribute('data-open')==='1')panelOpen(false);
  opener=D.activeElement;cmdk.setAttribute('data-open','1');input.value='';sel=0;render('');
  setTimeout(function(){input.focus();},10);live(STR.open||'Navigation open. Type to search.');
}
function cmdClose(){cmdk.setAttribute('data-open','0');if(opener&&opener.focus)try{opener.focus();}catch(e){}}
function bindCmd(){
  cmdk=$('#an-cmdk');input=$('#an-cmdk-in');listEl=$('#an-cmdk-list');
  input.addEventListener('input',function(){sel=0;render(input.value);});
  input.addEventListener('keydown',function(e){
    if(e.key==='ArrowDown'){e.preventDefault();sel=Math.min(sel+1,items.length-1);render(input.value);}
    else if(e.key==='ArrowUp'){e.preventDefault();sel=Math.max(sel-1,0);render(input.value);}
    else if(e.key==='Home'){e.preventDefault();sel=0;render(input.value);}
    else if(e.key==='End'){e.preventDefault();sel=items.length-1;render(input.value);}
    else if(e.key==='Enter'){e.preventDefault();choose(sel);}
    else if(e.key==='Escape'){e.preventDefault();cmdClose();}
  });
  listEl.addEventListener('click',function(e){var li=e.target.closest('li[data-i]');if(li)choose(+li.getAttribute('data-i'));});
  cmdk.addEventListener('mousedown',function(e){if(e.target===cmdk)cmdClose();});
}

/* ---- global keyboard ---- */
function inField(el){if(!el)return false;var t=(el.tagName||'').toLowerCase();return t==='input'||t==='textarea'||t==='select'||el.isContentEditable;}
function bindKeys(){
  D.addEventListener('keydown',function(e){
    if((e.metaKey||e.ctrlKey)&&(e.key==='k'||e.key==='K')){e.preventDefault();cmdOpen();return;}
    if(inField(e.target)){if(e.key==='Escape'&&cmdk.getAttribute('data-open')==='1')cmdClose();return;}
    if(e.key==='Escape'){if(cmdk.getAttribute('data-open')==='1')cmdClose();else if(panel.getAttribute('data-open')==='1')panelOpen(false);return;}
    if(e.key==='?'){e.preventDefault();cmdOpen();return;}
  });
}

function init(){enhance();applyPrefs();bindPanel();syncPanel();bindPanelToggle();buildDest();bindCmd();bindKeys();}
if(D.readyState==='loading')D.addEventListener('DOMContentLoaded',init);else init();
})();
