"""Mark editable leaf content in existing HTML. Existing markup and CSS remain intact."""
from pathlib import Path
from html.parser import HTMLParser
import re,json,html
root=Path(__file__).resolve().parent
void={'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'}
class Parser(HTMLParser):
    def __init__(self,s):
        super().__init__(convert_charrefs=False);self.s=s;self.lines=[0]+[m.end() for m in re.finditer('\n',s)];self.stack=[];self.nodes=[]
    def handle_starttag(self,tag,attrs):
        line,col=self.getpos();start=self.lines[line-1]+col;raw=self.get_starttag_text()
        n={'tag':tag,'attrs':dict(attrs),'start':start,'openend':start+len(raw),'parent':self.stack[-1] if self.stack else None,'children':[],'end':start+len(raw),'close':start+len(raw)}
        if n['parent']:n['parent']['children'].append(n)
        self.nodes.append(n)
        if tag not in void:self.stack.append(n)
    def handle_startendtag(self,tag,attrs):
        self.handle_starttag(tag,attrs)
        if tag not in void:self.stack.pop()
    def handle_endtag(self,tag):
        line,col=self.getpos();pos=self.lines[line-1]+col
        for i in range(len(self.stack)-1,-1,-1):
            if self.stack[i]['tag']==tag:
                n=self.stack[i];n['close']=pos;n['end']=self.s.find('>',pos)+1;del self.stack[i:];break
def ancestors(n):
    p=n['parent']
    while p:
        yield p;p=p['parent']
def classes(n):return (n['attrs'].get('class') or '').split()
manifest={}
for p in sorted(root.glob('*.html')):
    if p.name=='impressum.html':continue
    s=p.read_text()
    # Idempotence: deliberately refuse to renumber existing keys.
    if 'data-studio-field=' in s:raise RuntimeError('Already marked '+p.name)
    parser=Parser(s);parser.feed(s);fields={};sections={};changes=[]
    def mark(n,attr,key):changes.append((n['openend']-1,f' {attr}="{key}"'))
    def leaf(n):return not n['children'] and n['close']>n['openend']
    def inside(n,cls):return any(cls in classes(a) for a in ancestors(n))
    def text(n):return html.unescape(s[n['openend']:n['close']]).strip()
    for n in parser.nodes:
        a=list(ancestors(n))
        if not any(x['tag']=='main' for x in a):continue
        if any(x['tag'] in ('svg','form','noscript','script','style') or 'course-finder' in classes(x) or 'verein-feed' in classes(x) for x in a):continue
        tag=n['tag'];kind=None
        if tag=='img' and n['attrs'].get('data-asset') and n['attrs']['data-asset']!='logo' and not inside(n,'hero-subject') and not inside(n,'person'):
            kind='image';value=n['attrs'].get('src','')
            if not value.startswith('assets/') or not value.endswith('.webp'):continue
        elif leaf(n) and (tag in ('h1','h2','h3','p','figcaption','summary') or (tag in ('em','b') and any(x['tag'] in ('h1','h2','h3') for x in a)) or (tag in ('span','strong') and (inside(n,'schedule') or 'context-detail' in classes(n)))):
            value=text(n)
            if not value or len(value)>4000:continue
            kind='text'
        elif tag=='a' and not any(x['tag']=='a' for x in a) and ('btn' in classes(n) or 'textlink' in classes(n) or 'hero-secondary' in classes(n)):
            raw=s[n['openend']:n['close']]
            prefix=re.match(r'^([^<]+)',raw)
            if not prefix or not prefix.group(1).strip():continue
            kind='button';value=html.unescape(prefix.group(1)).strip()
        if kind:
            key='f%03d'%(len(fields)+1);fields[key]={'kind':kind,'label':('Bild' if kind=='image' else 'Button' if kind=='button' else 'Text'),'default':value}
            mark(n,'data-studio-field',key)
        if tag=='section' and n['parent'] and n['parent']['tag']=='main' and not ('hero' in classes(n) or 'pagelead' in classes(n)) and p.name in ('index.html','verein.html','aktuelles.html','galerie.html','training.html'):
            label=next((text(x) for x in n['children'] if x['tag']=='h2' and leaf(x)),None)
            if not label:
                label=next((text(x) for x in parser.nodes if x['tag']=='h2' and leaf(x) and n['start']<x['start']<n['end']),None)
            key='s%03d'%(len(sections)+1);sections[key]={'label':label or 'Abschnitt '+str(len(sections)+1)};mark(n,'data-studio-section',key)
    for pos,addition in sorted(changes,reverse=True):s=s[:pos]+addition+s[pos:]
    s=s.replace('</head>','<script src="website-content.js?v=1" defer></script></head>',1)
    p.write_text(s)
    manifest[p.name]={'fields':fields,'sections':sections}
(root/'studio-manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n')
print('Editable pages:',len(manifest),'fields:',sum(len(x['fields']) for x in manifest.values()))
