"""Build two perfectly aligned layers from the original action photo."""
from pathlib import Path
from io import BytesIO
import json
import requests
import numpy as np
import cv2
from PIL import Image, ImageOps, ImageFilter
from rembg import remove, new_session

ROOT=Path(__file__).resolve().parent
out=ROOT/"generated-media"
out.mkdir(exist_ok=True)
url=json.loads((ROOT/"assets.json").read_text())["jump"]
response=requests.get(url,headers={"User-Agent":"THV website photo processing/1.0"},timeout=45)
response.raise_for_status()
with Image.open(BytesIO(response.content)) as raw:
    original=ImageOps.exif_transpose(raw).convert("RGB")
original.thumbnail((1600,1600),Image.Resampling.LANCZOS)
w,h=original.size
cut=remove(original,session=new_session("isnet-general-use"),post_process_mask=False).convert("RGBA")
mask=np.asarray(cut.getchannel("A"))
fraction=float((mask>32).mean())
if not 0.025 < fraction < .88:
    raise RuntimeError(f"Unusable foreground fraction {fraction}")
# Keep the original RGB pixels, preserve the uncropped placement and exact canvas.
foreground=original.convert("RGBA")
foreground.putalpha(cut.getchannel("A"))
# Work on a smaller plane for efficient background reconstruction.
f=min(1.0,640/max(w,h))
sw,sh=max(1,round(w*f)),max(1,round(h*f))
small=cv2.resize(np.asarray(original),(sw,sh),interpolation=cv2.INTER_AREA)
alpha=cv2.resize(mask,(sw,sh),interpolation=cv2.INTER_AREA)
binary=np.uint8(alpha>18)*255
binary=cv2.dilate(binary,cv2.getStructuringElement(cv2.MORPH_ELLIPSE,(15,15)))
clean=cv2.inpaint(small,binary,5,cv2.INPAINT_TELEA)
clean=Image.fromarray(clean).resize((w,h),Image.Resampling.BICUBIC)
clean=clean.filter(ImageFilter.GaussianBlur(max(5,round(max(w,h)*.012))))
# Restore the original surrounding tones while softening scene details uniformly.
clean.save(out/"hero-depth-background.webp",format="WEBP",quality=87,method=6)
foreground.save(out/"hero-depth-dog.webp",format="WEBP",quality=94,method=6,alpha_quality=100)
print(f"Aligned photo layers {w} x {h}; foreground {fraction:.3f}")
