"""Run once with internet access and permission to reuse original photographs."""
from pathlib import Path
import json,urllib.request, urllib.parse
from PIL import Image
P=Path(__file__).parent; A=P/'assets';A.mkdir(exist_ok=True)
manifest=json.loads((P/'assets.json').read_text())
for k,url in manifest.items():
 try:
  req=urllib.request.Request(url,headers={'User-Agent':'Mozilla/5.0'})
  import io
  with urllib.request.urlopen(req,timeout=35) as r: im=Image.open(io.BytesIO(r.read()))
  im.thumbnail((1500,1500)); im.save(A/(k+'.webp'),'WEBP',quality=87)
  for f in P.glob('*.html'):
   s=f.read_text();s=s.replace(url,'assets/'+k+'.webp');f.write_text(s)
  print('OK',k)
 except Exception as ex: print('FAILED',k,ex)
