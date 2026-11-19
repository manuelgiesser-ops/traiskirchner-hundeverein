<?php
declare(strict_types=1);
require __DIR__.'/studio-bootstrap.php';
thv_headers();header('Content-Type: application/json; charset=utf-8');thv_session();
try{
    if($_SERVER['REQUEST_METHOD']!=='POST'||!thv_secure())throw new DomainException('HTTPS erforderlich.');
    thv_check();$s=thv_state(fn($s)=>$s);thv_need(thv_user($s),'admin','editor');
    if(!function_exists('imagewebp')||!function_exists('imagecreatefromstring'))throw new DomainException('Auf dem Hosting muss PHP GD mit WebP aktiviert sein.');
    $file=$_FILES['image']??null;
    if(!$file||$file['error']!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name'])||$file['size']>12000000)throw new DomainException('Bitte wähle ein JPG, PNG oder WebP bis 12 MB.');
    $info=@getimagesize($file['tmp_name']);
    if(!$info||!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true)||$info[0]*$info[1]>20000000)throw new DomainException('Ungültiges Bild oder mehr als 20 Megapixel.');
    $raw=file_get_contents($file['tmp_name']);$src=@imagecreatefromstring($raw);unset($raw);if(!$src)throw new DomainException('Bild konnte nicht gelesen werden.');
    if($info[2]===IMAGETYPE_JPEG&&function_exists('exif_read_data')){ $exif=@exif_read_data($file['tmp_name']);$o=$exif['Orientation']??1;
        if(in_array($o,[2,4,5,7],true))imageflip($src,IMG_FLIP_HORIZONTAL);
        if(in_array($o,[3,4],true))$src=imagerotate($src,180,0);
        elseif(in_array($o,[5,6],true))$src=imagerotate($src,-90,0);
        elseif(in_array($o,[7,8],true))$src=imagerotate($src,90,0);
    }
    $w=imagesx($src);$h=imagesy($src);$ratio=min(1,1600/max($w,$h));$tw=max(1,(int)round($w*$ratio));$th=max(1,(int)round($h*$ratio));
    $dst=imagecreatetruecolor($tw,$th);imagealphablending($dst,false);imagesavealpha($dst,true);imagefill($dst,0,0,imagecolorallocatealpha($dst,0,0,0,127));
    imagecopyresampled($dst,$src,0,0,0,0,$tw,$th,$w,$h);imagedestroy($src);
    $id=thv_id();$path=thv_media_dir().'/'.$id.'.webp';
    if(!imagewebp($dst,$path,84)){imagedestroy($dst);throw new RuntimeException('Bild konnte nicht gespeichert werden.');}imagedestroy($dst);chmod($path,0600);
    if(!filesize($path)||filesize($path)>3000000){unlink($path);throw new DomainException('Das aufbereitete Bild ist zu groß.');}
    $name=trim((string)($_POST['name']??''));if($name===''||strlen($name)>180||!preg_match('//u',$name)){$name='Neues Vereinsbild';}
    try{thv_state(function(&$s)use($id,$name,$tw,$th,$path){thv_need(thv_user($s),'admin','editor');$s['media']??=[];if(count($s['media'])>=200)throw new DomainException('Das Medienarchiv enthält bereits 200 Bilder.');$total=array_sum(array_column($s['media'],'bytes'));if($total+filesize($path)>60000000)throw new DomainException('Das Medienarchiv ist voll.');$s['media'][$id]=['name'=>$name,'width'=>$tw,'height'=>$th,'bytes'=>filesize($path)];},true);}catch(Throwable $e){unlink($path);throw $e;}
    echo json_encode(['url'=>thv_media_url($id),'name'=>$name,'width'=>$tw,'height'=>$th,'bytes'=>filesize($path)],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
}catch(DomainException $e){http_response_code(400);echo json_encode(['error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}catch(Throwable $e){http_response_code(503);error_log('THV upload: '.$e->getMessage());echo '{"error":"Upload momentan nicht verfügbar."}';}
