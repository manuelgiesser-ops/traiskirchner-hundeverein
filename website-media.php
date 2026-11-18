<?php
declare(strict_types=1);
require __DIR__.'/verein-core.php';
header('X-Content-Type-Options: nosniff');header('Cache-Control: no-store');header("Content-Security-Policy: default-src 'none'; sandbox");
$id=(string)($_GET['id']??'');if(!preg_match('/^[a-f0-9]{24}$/',$id)){http_response_code(404);exit;}
try{
    $s=thv_state(fn($s)=>$s);if(!isset($s['media'][$id]))throw new RuntimeException('Unknown media');
    $public=thv_media_public($s,$id);
    if(!$public){thv_session();$u=thv_user($s);if(!thv_role($u,'admin','editor')){
        $allowed=false;foreach($s['entries'] as $e)if(($e['image']??'')===thv_media_url($id)&&$e['visibility']==='members'&&thv_role($u,'member'))$allowed=true;
        if(!$allowed)throw new RuntimeException('Forbidden');
    }}
    $path=thv_media_dir().'/'.$id.'.webp';if(!is_file($path)||is_link($path))throw new RuntimeException('Media missing');
    header('Content-Type: image/webp');header('Content-Length: '.filesize($path));readfile($path);
}catch(Throwable $e){http_response_code(404);}
