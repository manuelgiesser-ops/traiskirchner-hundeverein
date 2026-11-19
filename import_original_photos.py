"""Import a curated set of photos from the original THV 2022 gallery.
Source attribution is preserved in assets.json and the import report.
Only valid images are published; no fabricated or placeholder photos.
"""
from pathlib import Path
from urllib.request import Request,urlopen
from io import BytesIO
from PIL import Image,ImageOps
from html import escape
import json,re,time
from concurrent.futures import ThreadPoolExecutor
root=Path(__file__).resolve().parent
sources=[
 ('archive-junghund-2022','ic71fd0f895d4ee84','1684336469','jpg','425','Junghundekurs 2022'),
 ('archive-longieren-2022','i0765b825ed9c1547','1684336787','png','375','Dog Activity Longieren 2022'),
 ('archive-jhv-2022','ia185a2c25ef632a6','1684336469','jpg','425','Junghundeveranlagung 2022'),
 ('archive-turnier-2022','i6da1f021d798444c','1684336469','jpg','425','Vereinsturnier 2022'),
 ('archive-agility-2022','if5d52e6751817562','1684336469','jpg','425','Abschlussturnier Agility 2022'),
 ('archive-abschluss-2022','i1e74fb32a09170e2','1684336469','jpg','425','Kursabschluss Herbst 2022')
]
source_page='https://traiskirchner-hundeverein.jimdofree.com/galerie/2022/'
def get(row):
    key,id,version,ext,size,label=row
    suffix=f'/path/s304ff6c883af050c/image/{id}/version/{version}/image.{ext}'
    base='https://image.jimcdn.com/app/cms/image/transf/'
    urls=[base+'none'+suffix,base+f'dimension%3D{size}x1024%3Aformat%3D{ext}'+suffix]
    for url in urls:
        try:
            with urlopen(Request(url,headers={'User-Agent':'Mozilla/5.0','Referer':source_page}),timeout=30) as f:raw=f.read(18000000)
            with Image.open(BytesIO(raw)) as im:
                im=ImageOps.exif_transpose(im).convert('RGB')
            if min(im.size)<120:raise ValueError('Image too small')
            variants=[]
            for limit in (480,960,1600):
                image=im.copy(); image.thumbnail((limit,limit),Image.Resampling.LANCZOS)
                name=f'assets/{key}-{limit}.webp';image.save(root/name,'WEBP',quality=86,method=5)
                variants.append(dict(file=name,w=image.width,h=image.height,bytes=(root/name).stat().st_size))
            return dict(key=key,label=label,source=url,original=list(im.size),variants=variants)
        except Exception as e: print('SOURCE FAILED',key,url,str(e),flush=True)
    return None
(root/'assets').mkdir(exist_ok=True)
with ThreadPoolExecutor(max_workers=4) as ex:results=[r for r in ex.map(get,sources) if r]
if not results:raise SystemExit('No original photos could be downloaded.')
manifest=json.loads((root/'studio-manifest.json').read_text())
assets=json.loads((root/'assets.json').read_text())
for r in results:assets[r['key']]=r['source']
def photo(row,key,caption=True):
    v=row['variants'];mid=v[1];full=v[-1];label=escape(row['label'],quote=True)
    widths={x['w']:x['file'] for x in v}
    srcset=', '.join(f'{file} {w}w' for w,file in sorted(widths.items()))
    return f'<img src="{mid["file"]}" alt="{label}" loading="lazy" decoding="async" width="{mid["w"]}" height="{mid["h"]}" srcset="{srcset}" sizes="(max-width:760px) 92vw, 420px" data-full="{full["file"]}" data-asset="{row["key"]}" data-studio-field="{key}">'
def field(page,key,row):
    manifest[page]['fields'][key]={'kind':'image','label':'Originalfoto','default':row['variants'][1]['file']}
def section(page,rows,heading,anchor,sectionid=None):
    p=root/page;s=p.read_text()
    if 'data-original-archive="2022"' in s:return False
    if not rows:return False
    items=[]
    for i,row in enumerate(rows):
        key=f'f{900+i}';field(page,key,row)
        items.append(f'<figure class="archive-photo"><img-placeholder></img-placeholder><figcaption>{escape(row["label"])}</figcaption></figure>'.replace('<img-placeholder></img-placeholder>',photo(row,key)))
    secattr=''
    if sectionid:
        secattr=f' data-studio-section="{sectionid}"';manifest[page]['sections'][sectionid]={'label':heading}
    section=f'<section class="section cream archive-section" data-original-archive="2022"{secattr}><div class="wrap"><div class="section-head"><h2>{escape(heading)}</h2><p>Einblicke aus unserem Vereinsarchiv.</p></div><div class="archive-grid">{"".join(items)}</div></div></section>'
    if anchor not in s:raise RuntimeError('Missing anchor '+page)
    p.write_text(s.replace(anchor,section+anchor,1));return True
# The archived gallery is integrated in a distinct section rather than mixed with new photos.
section('galerie.html',results,'Aus dem Vereinsarchiv','</main>','s090')
# Additional photos on selected pages preserve the original context and date.
indexed={r['key']:r for r in results}
def pick(*keys):return [indexed[k] for k in keys if k in indexed]
section('index.html',pick('archive-longieren-2022','archive-turnier-2022','archive-abschluss-2022'),'Einblicke ins Vereinsleben','<section class="section dark decorated-section final-contact"','s090')
section('welpen.html',pick('archive-junghund-2022','archive-jhv-2022'),'Unsere jungen Hunde im Training','<section class="section course-return">')
section('agility.html',pick('archive-agility-2022'),'Einblick ins Agility-Training','<section class="section course-return">')
(root/'studio-manifest.json').write_text(json.dumps(manifest,ensure_ascii=False,indent=2)+'\n')
(root/'assets.json').write_text(json.dumps(assets,ensure_ascii=False,indent=2)+'\n')
# Cache versions for all pages, including the public studio renderer.
for page in root.glob('*.html'):
    s=page.read_text()
    s=re.sub(r'(style\.css|site\.js)\?v=\d+',r'\1?v=37',s)
    s=re.sub(r'website-content\.js\?v=\d+','website-content.js?v=9',s)
    page.write_text(s)
report={'source_page':source_page,'imported':len(results),'requested':len(sources),'photos':results,'failed':[x[0] for x in sources if x[0] not in indexed]}
(root/'photo-import-report.json').write_text(json.dumps(report,ensure_ascii=False,indent=2)+'\n')
print('Imported',len(results),'/',len(sources),'original photos')
