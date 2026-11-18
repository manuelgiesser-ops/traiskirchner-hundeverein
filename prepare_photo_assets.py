"""Create a transparent original-photo cutout and offline face crop coordinates.
No identification or face embeddings; only bounding rectangles.
"""
from pathlib import Path
from io import BytesIO
import json
import requests
import cv2
import numpy as np
from PIL import Image, ImageOps
from rembg import remove, new_session

ROOT = Path(__file__).resolve().parent
OUT = ROOT / "generated-media"
OUT.mkdir(exist_ok=True)
ASSETS = json.loads((ROOT / "assets.json").read_text())
TRAINERS = ("sari", "michi", "harald", "karo", "otto", "mary", "susi", "alex", "sabrina", "conny", "patrick", "gabi")
http = requests.Session()
http.headers.update({"User-Agent": "THV website image preparation/1.0"})

def read_photo(key):
    resp = http.get(ASSETS[key], timeout=35)
    resp.raise_for_status()
    with Image.open(BytesIO(resp.content)) as im:
        return ImageOps.exif_transpose(im).convert("RGB")

def prepare_faces():
    model = "https://media.githubusercontent.com/media/opencv/opencv_zoo/main/models/face_detection_yunet/face_detection_yunet_2023mar.onnx"
    path = OUT / ".yunet.onnx"
    detector = None
    try:
        r = http.get(model, timeout=35)
        r.raise_for_status()
        path.write_bytes(r.content)
        detector = cv2.FaceDetectorYN.create(str(path), "", (320,320), 0.75, 0.3, 5000)
    except Exception as exc:
        print("YuNet unavailable:", type(exc).__name__)
    haar = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
    result = {}
    for key in TRAINERS:
        try:
            im=read_photo(key)
            w0,h0=im.size
            im.thumbnail((1200,1200),Image.Resampling.LANCZOS)
            w,h=im.size
            arr=cv2.cvtColor(np.array(im),cv2.COLOR_RGB2BGR)
            boxes=[]
            if detector is not None:
                detector.setInputSize((w,h))
                _, rows=detector.detect(arr)
                if rows is not None:
                    boxes=[list(map(float,v[:4])) for v in rows if v[-1] >= .75]
            if not boxes:
                gray=cv2.cvtColor(arr,cv2.COLOR_BGR2GRAY)
                boxes=[list(map(float,b)) for b in haar.detectMultiScale(gray,1.08,5,minSize=(25,25))]
            if not boxes:
                print(key, "no confident detection")
                continue
            boxes.sort(key=lambda v:v[2]*v[3],reverse=True)
            main=boxes[0]
            chosen=[b for b in boxes if b[2]*b[3] >= .55*main[2]*main[3]]
            l=min(b[0] for b in chosen); t=min(b[1] for b in chosen)
            r=max(b[0]+b[2] for b in chosen); bot=max(b[1]+b[3] for b in chosen)
            result[key]={"x":round((l+r)/2/w,5),"y":round((t+bot)/2/h,5),"width":round((r-l)/w,5),"height":round((bot-t)/h,5)}
            print(key,result[key])
        except Exception as exc:
            print(key, type(exc).__name__, str(exc)[:160])
    path.unlink(missing_ok=True)
    return result

def prepare_hero():
    im=read_photo("jump")
    im.thumbnail((1400,1400),Image.Resampling.LANCZOS)
    cut=remove(im,session=new_session("isnet-general-use"),post_process_mask=False).convert("RGBA")
    alpha=np.array(cut.getchannel("A"))
    visible=(alpha>32).mean()
    if not .025 < visible < .93:
        raise ValueError("Mask does not isolate a meaningful subject")
    box=cut.getchannel("A").point(lambda a: 255 if a>24 else 0).getbbox()
    if not box:
        raise ValueError("Empty mask")
    cut=cut.crop(box)
    pad=max(10,round(max(cut.size)*.04))
    layer=Image.new("RGBA",(cut.width+2*pad,cut.height+2*pad))
    layer.alpha_composite(cut,(pad,pad))
    layer.save(OUT/"hero-dog.webp",format="WEBP",quality=92,method=6,alpha_quality=100)
    print("Hero cutout generated",layer.size,"foreground",round(float(visible),3))
    return "hero-dog.webp"

old = {}
config = OUT / "photo-focus.json"
if config.exists():
    old=json.loads(config.read_text())
faces=prepare_faces()
old["faces"]={**old.get("faces",{}),**faces}
try:
    old["hero"]=prepare_hero()
except Exception as exc:
    print("Hero processing failed:",type(exc).__name__,str(exc)[:200])
config.write_text(json.dumps(old,indent=2)+"\n")
if not old.get("faces") and not old.get("hero"):
    raise RuntimeError("No usable photo assets were created; originals remain active.")
