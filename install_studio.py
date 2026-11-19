"""Mark editable leaf content in existing HTML. Existing markup and CSS remain intact."""
from pathlib import Path
from html.parser import HTMLParser
import re,json,html,sys,argparse,shutil,hashlib,os
source=Path(__file__).resolve().parent
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

def main():
    ap=argparse.ArgumentParser(description='Install a separate Website Studio for existing root-level HTML pages.')
    ap.add_argument('target',type=Path)
    ap.add_argument('--name',required=True)
    ap.add_argument('--storage',required=True,type=Path)
    ap.add_argument('--document-root',required=True,type=Path)
    args=ap.parse_args()
    root=args.target.resolve();disk=args.storage.resolve();web=args.document_root.resolve()
    if root==source or not root.is_dir() or not (root/'index.html').is_file():
        raise SystemExit('Choose a different existing website with index.html.')
    if root!=web and web not in root.parents:raise SystemExit('Target must be within the actual document root.')
    if disk==web or web in disk.parents or disk==root:raise SystemExit('Storage must be outside document root.')
    files=['website-studio.php','website-studio.js','website-studio.css','studio-interface.css','studio-bootstrap.php','studio-standalone-core.php','studio-remember.php','studio-login.php','studio-user-cli.php','website-content.js','studio-api.php','studio-upload.php','studio-blocks-lib.php','studio-seo-lib.php','studio-page.php','studio-media-lib.php','website-media.php','studio-blocks.css']
    if any((root/f).exists() for f in files+['studio-site.php','studio-manifest.json']):raise SystemExit('An editor already exists. Back up and migrate manually; no overwrite.')
    pages=sorted(root.glob('*.html'))
    work={};manifest={}
    for p in pages:
        s=p.read_text(encoding='utf-8')
        if 'data-studio-field=' in s or 'data-studio-section=' in s:raise SystemExit('Existing editor markers: '+p.name)
        if not re.fullmatch(r'[a-zA-Z0-9_-]+\.html',p.name):raise SystemExit('Unsupported page filename: '+p.name)
        parser=Parser(s);parser.feed(s);fields={};sections={};changes=[];groups={}
        def mark(n,attr,key):
            pos=n['openend']-1
            if s[pos-1:pos]=='/':pos-=1
            changes.append((pos,f' {attr}="{key}"'))
        def leaf(n):return not n['children'] and n['close']>n['openend']
        def text(n):return html.unescape(s[n['openend']:n['close']]).strip()
        for n in parser.nodes:
            ancestors_=list(ancestors(n))
            if not any(x['tag']=='main' for x in ancestors_):continue
            if any(x['tag'] in ('svg','form','noscript','script','style','button') for x in ancestors_) or any('data-studio-protect' in x['attrs'] for x in [n]+ancestors_):continue
            tag=n['tag'];kind=None
            if tag=='img':
                value=n['attrs'].get('src','')
                if re.fullmatch(r'(?:[a-zA-Z0-9_-]+/)*[a-zA-Z0-9_.-]+\.(?:webp|avif|png|jpe?g|gif)',value,re.I) and (root/value).is_file():kind='image'
            elif tag in ('h1','h2','h3','h4','p','figcaption','li','summary','blockquote') and leaf(n):
                value=text(n)
                if value and len(value)<=4000:kind='text'
            elif tag=='a' and ('btn' in classes(n) or 'textlink' in classes(n) or 'button' in classes(n)):
                prefix=re.match(r'^([^<]+)',s[n['openend']:n['close']])
                if prefix and prefix.group(1).strip():kind='button';value=html.unescape(prefix.group(1)).strip()
            if kind:
                key='f%03d'%(len(fields)+1);fields[key]={'kind':kind,'label':{'image':'Bild','button':'Button','text':'Text'}[kind],'default':value};mark(n,'data-studio-field',key);n['studio_field']=key
            if tag=='section' and n['parent'] and n['parent']['tag']=='main' and not any(x in classes(n) for x in ('hero','pagelead')):
                label=next((text(x) for x in parser.nodes if x['tag'] in ('h2','h3') and leaf(x) and n['start']<x['start']<n['end']),None)
                key='s%03d'%(len(sections)+1);sections[key]={'label':label or 'Abschnitt '+str(len(sections)+1)};mark(n,'data-studio-section',key)
        # Mark only safe, adjacent editable text/button elements and simple image/text columns.
        protected={'hero','pagelead','course','course-directory','course-modern','course-finder','people','person','faq','gallery-grid','archive-grid','verein-feed','steps','schedule','field-story','facility-grid','course-return','bond-story','final-photo-contact'}
        def safe(n):
            return not any(x['tag'] in ('form','svg','button','details','nav','dialog','ul','ol') or protected.intersection(classes(x)) or 'data-studio-protect' in x['attrs'] for x in [n]+list(ancestors(n)))
        def has_field(n):
            return bool(n.get('studio_field')) or any(has_field(c) for c in n['children'])
        def add_group(units,kind):
            if len(units)<2:return
            gid='g%03d'%(len(groups)+1);ids=[]
            for i,n in enumerate(units,1):
                uid=gid+'u'+str(i);ids.append(uid);mark(n,'data-studio-unit',uid);mark(n,'data-studio-group',gid)
            groups[gid]={'kind':kind,'units':ids}
        for n in parser.nodes:
            if not safe(n) or not any(x['tag']=='main' for x in [n]+list(ancestors(n))):continue
            ch=n['children']
            if 'split' in classes(n) and len(ch)==2 and all(c['tag'] in ('div','figure') and safe(c) and has_field(c) for c in ch) and any('photo' in classes(c) for c in ch) and any('prose' in classes(c) for c in ch):
                add_group(ch,'columns')
            run=[]
            for c in ch:
                if c.get('studio_field') and c['tag'] in ('h2','h3','h4','p','a') and safe(c):run.append(c)
                else:add_group(run,'flow');run=[]
            add_group(run,'flow')
        if not fields and not sections:continue
        if '</head>' not in s:raise SystemExit('Missing </head>: '+p.name)
        for pos,addition in sorted(changes,reverse=True):s=s[:pos]+addition+s[pos:]
        title=next((text(n) for n in parser.nodes if n['tag']=='h1' and leaf(n)),p.stem)
        manifest[p.name]={'label':title,'fields':fields,'sections':sections,'layoutGroups':groups}
        work[p]=s.replace('</head>','<link rel="stylesheet" href="studio-blocks.css?v=3"><script src="website-content.js?v=54" defer></script></head>',1)
    if not work:raise SystemExit('No editable content in main elements.')
    disk.mkdir(parents=True,exist_ok=True,mode=0o700)
    if any(disk.iterdir()):raise SystemExit('Private storage must be empty to avoid sharing another site’s data.')
    (disk/'studio-site.id').write_text(hashlib.sha256(str(root).encode()).hexdigest())
    os.chmod(disk/'studio-site.id',0o600)
    def phpquote(x):return "'"+str(x).replace("\\","\\\\").replace("'","\\'")+"'"
    config="<?php\nreturn ['adapter'=>'standalone','name'=>"+phpquote(args.name)+",'storage'=>"+phpquote(disk)+"];\n"
    backup=disk/'original-html';backup.mkdir(mode=0o700)
    for p,s in work.items():shutil.copy2(p,backup/p.name)
    for f in files:shutil.copy2(source/f,root/f)
    (root/'studio-site.php').write_text(config,encoding='utf-8')
    (root/'studio-manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    for p,s in work.items():p.write_text(s,encoding='utf-8')
    with (root/'.htaccess').open('a',encoding='utf-8') as fh:fh.write("\n# Website Studio: render published SEO metadata before the browser runs JavaScript.\n<IfModule mod_rewrite.c>\n  RewriteEngine On\n  RewriteRule ^$ studio-page.php?page=index.html [END]\n  RewriteCond %{REQUEST_FILENAME} -f\n  RewriteRule ^([A-Za-z0-9_-]+\\.html)$ studio-page.php?page=$1 [END]\n</IfModule>\n")
    print('Installed',len(work),'pages. Create your own account with studio-user-cli.php. Original HTML backed up in private storage.')
if __name__=='__main__':main()
