<?php
declare(strict_types=1);
require __DIR__.'/verein-core.php';
thv_headers();header('Content-Type: application/json; charset=utf-8');
function studio_out($value,int $status=200): never {http_response_code($status);echo json_encode($value,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;}
function studio_manifest(): array {return json_decode((string)file_get_contents(__DIR__.'/studio-manifest.json'),true,512,JSON_THROW_ON_ERROR);}
function studio_media(): array {
    $out=[];foreach(glob(__DIR__.'/assets/*-960.webp')?:[] as $p){$rel='assets/'.basename($p);$out[$rel]=str_replace('-960.webp','',basename($p));}try{return $out+thv_state(fn($s)=>thv_media_choices($s));}catch(Throwable $e){return $out;}
}
function studio_validate(array $content,array $manifest,array $media): array {
    $out=[];
    foreach($content as $page=>$patch){
        if(!isset($manifest[$page])||!is_array($patch))throw new DomainException('Unbekannte Seite.');
        $fields=[];foreach(($patch['fields']??[]) as $key=>$value){
            $def=$manifest[$page]['fields'][$key]??null;if(!$def||!is_string($value)||!preg_match('//u',$value))throw new DomainException('Ungültiger Inhalt.');
            if($def['kind']==='image'){if(!isset($media[$value]))throw new DomainException('Bild nicht verfügbar.');}
            elseif(strlen($value)>12000||trim($value)==='')throw new DomainException('Bitte einen gültigen Text eingeben.');
            if($value!==$def['default'])$fields[$key]=$value;
        }
        $sections=array_keys($manifest[$page]['sections']);$hidden=$patch['hidden']??[];$order=$patch['order']??[];
        if(!is_array($hidden)||!is_array($order)||count(array_unique($hidden))!==count($hidden)||count(array_unique($order))!==count($order)||array_diff($hidden,$sections)||array_diff($order,$sections))throw new DomainException('Abschnitt nicht verfügbar.');
        if($order&&count($order)!==count($sections))throw new DomainException('Abschnittsreihenfolge unvollständig.');
        $out[$page]=['fields'=>$fields,'hidden'=>array_values($hidden),'order'=>array_values($order)];
    }return $out;
}
try{
    if($_SERVER['REQUEST_METHOD']==='GET'){
        // Public content contains only published, allowlisted overrides. No sessions or private data.
        if(($_GET['mode']??'')==='public'){
            header('Cache-Control: no-cache');$page=(string)($_GET['page']??'');
            try{$p=thv_state(fn($s)=>$s['studio']['published'][$page]??null);}catch(Throwable $e){$p=null;}
            studio_out(['patch'=>$p]);
        }
        thv_session();$s=thv_state(fn($s)=>$s);thv_need(thv_user($s),'admin','editor');$studio=$s['studio']??[];
        studio_out(['csrf'=>$_SESSION['csrf'],'manifest'=>studio_manifest(),'media'=>studio_media(),'draft'=>$studio['draft']??$studio['published']??[],'published'=>$studio['published']??[],'revision'=>$studio['revision']??0,'history'=>array_map(fn($v)=>['id'=>$v['id'],'date'=>$v['date'],'author'=>$v['author']],$studio['history']??[]),'admin'=>thv_role(thv_user($s),'admin')]);
    }
    if($_SERVER['REQUEST_METHOD']!=='POST'||!thv_secure())studio_out(['error'=>'HTTPS erforderlich.'],403);
    thv_session();$raw=file_get_contents('php://input',false,null,0,2000001);if(strlen($raw)>2000000)throw new DomainException('Änderungen zu groß.');
    $data=json_decode($raw,true,512,JSON_THROW_ON_ERROR);if(!is_array($data)||!is_string($data['csrf']??null)||!hash_equals($_SESSION['csrf'],$data['csrf']))studio_out(['error'=>'Bitte erneut anmelden.'],403);
    $action=$data['action']??'';$manifest=studio_manifest();$media=studio_media();
    $new=thv_state(function(&$s)use($action,$data,$manifest,$media){
        $u=thv_user($s);thv_need($u,'admin','editor');
        $st=$s['studio']??['published'=>[],'draft'=>[],'history'=>[],'revision'=>0];
        if(($data['revision']??-1)!==($st['revision']??0))throw new DomainException('Eine andere Person hat Änderungen gespeichert. Bitte lade das Studio neu.');
        if($action==='restore'){
            $found=null;foreach($st['history'] as $v)if($v['id']===($data['id']??''))$found=$v;
            if(!$found)throw new DomainException('Version nicht gefunden.');
            $st['draft']=studio_validate($found['content'],$manifest,$media);
        }elseif($action==='save'||$action==='publish'){
            if(!is_array($data['draft']??null))throw new DomainException('Ungültiger Entwurf.');
            $st['draft']=studio_validate($data['draft'],$manifest,$media);
            if($action==='publish'){
                thv_need($u,'admin');
                $st['history'][]=['id'=>thv_id(),'date'=>date(DATE_ATOM),'author'=>$u['name'],'content'=>$st['published']];
                $st['history']=array_slice($st['history'],-20);$st['published']=$st['draft'];
            }
        }else throw new DomainException('Unbekannte Aktion.');
        $st['revision']=($st['revision']??0)+1;$s['studio']=$st;return $st;
    },true);
    studio_out(['revision'=>$new['revision'],'draft'=>$new['draft'],'published'=>$new['published'],'history'=>array_map(fn($v)=>['id'=>$v['id'],'date'=>$v['date'],'author'=>$v['author']],$new['history'])]);
}catch(DomainException $e){studio_out(['error'=>$e->getMessage()],403);}catch(Throwable $e){error_log('THV studio: '.$e->getMessage());studio_out(['error'=>'Studio momentan nicht verfügbar.'],503);}
