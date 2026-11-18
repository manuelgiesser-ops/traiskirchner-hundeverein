"""Build small local responsive WebP photos, preserving full views.
Original image URLs remain in assets.json as reproducible source references.
Failed sources continue to use their existing URLs.
"""
from pathlib import Path
from io import BytesIO
from concurrent.futures import ThreadPoolExecutor
import json, re, time
from urllib.request import Request, urlopen
from PIL import Image, ImageOps

root=Path(__file__).resolve().parent
out=root/'assets'
out.mkdir(exist_ok=True)
sources={k:v for k,v in json.loads((root/'assets.json').read_text()).items() if v.startswith(('https://','http://'))}

def convert(item):
    key,url=item
    for attempt in range(3):
        try:
            with urlopen(Request(url,headers={'User-Agent':'Mozilla/5.0'}),timeout=50) as res:
                raw=res.read()
            with Image.open(BytesIO(raw)) as im:
                image=ImageOps.exif_transpose(im).convert('RGB')
            full_limit=2400 if key=='team' else 1600
            variants=[]
            for width in [480,960,full_limit]:
                version=image.copy()
                version.thumbnail((width,width),Image.Resampling.LANCZOS)
                w,h=version.size
                if any(v['w']==w for v in variants):
                    continue
                name=f'assets/{key}-{width}.webp'
                version.save(root/name,'WEBP',quality=88 if width==full_limit else 83,method=6)
                variants.append({'file':name,'w':w,'h':h,'bytes':(root/name).stat().st_size})
            assert variants
            data={'original_bytes':len(raw),'width':image.width,'height':image.height,'variants':variants}
            print('OK',key, image.size, len(raw), [v['bytes'] for v in variants],flush=True)
            return key,data
        except Exception as exc:
            if attempt==2:
                print('FAILED',key,str(exc),flush=True)
                return key,None
            time.sleep(1.5*(attempt+1))

with ThreadPoolExecutor(max_workers=5) as executor:
    results={k:v for k,v in executor.map(convert,sources.items()) if v}

def replace_attr(tag,name,value):
    pat=r'\s'+re.escape(name)+r'=(?:"[^"]*"|[^\s>]+)'
    if re.search(pat,tag):
        return re.sub(pat,lambda _:f' {name}="{value}"',tag,count=1)
    return tag[:-1]+f' {name}="{value}">'

for path in sorted(root.glob('*.html')):
    s=path.read_text(encoding='utf-8')
    def image_tag(match):
        tag=match.group()
        found=re.search(r'\bdata-asset="([^"]+)"',tag)
        if not found or found.group(1) not in results:
            return tag
        key=found.group(1)
        data=results[key]
        variants=data['variants']
        middle=variants[min(1,len(variants)-1)]
        full=variants[-1]
        eager='fetchpriority' in tag
        tag=replace_attr(tag,'src',middle['file'])
        tag=replace_attr(tag,'srcset',', '.join(f"{v['file']} {v['w']}w" for v in variants))
        sizes='(max-width: 760px) 92vw, 650px' if eager or key=='team' else '(max-width: 760px) 48vw, (max-width: 1050px) 40vw, 420px'
        # Non-card sections display larger photos, so use their reading width.
        if key in ('field','house','run'):
            sizes='(max-width: 760px) 92vw, 650px'
        tag=replace_attr(tag,'sizes',sizes)
        tag=replace_attr(tag,'data-full',full['file'])
        tag=replace_attr(tag,'width',str(middle['w']))
        tag=replace_attr(tag,'height',str(middle['h']))
        tag=replace_attr(tag,'decoding','async')
        if not eager:
            tag=replace_attr(tag,'loading','lazy')
        return tag
    s=re.sub(r'<img\b[^>]*>',image_tag,s)
    for key,data in results.items():
        url=sources[key]
        variants=data['variants']
        middle=variants[min(1,len(variants)-1)]['file']
        full=variants[-1]['file']
        for quote in ("'",'"'):
            s=s.replace(f"url({quote}{url}{quote})",f"url({quote}{middle}{quote})")
        s=s.replace(f'data-full="{url}"',f'data-full="{full}"')
    # Social share metadata must be an absolute URL; keep the original for now.
    s=re.sub(r'<link rel="preconnect" href="https://image.jimcdn.com"[^>]*>', '', s) if len(results)==len(sources) else s
    path.write_text(s,encoding='utf-8')
report={'optimized':len(results),'total':len(sources),'failed':sorted(set(sources)-set(results)),'images':results}
(root/'performance-report.json').write_text(json.dumps(report,indent=2)+'\n',encoding='utf-8')
print('Optimized',len(results),'of',len(sources),'originals',flush=True)
if not results:
    raise SystemExit('No source images available; no image replacement published.')
