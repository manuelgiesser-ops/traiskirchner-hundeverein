<?php
declare(strict_types=1);
function thv_media_dir(): string {
    $dir=thv_private().'/media';
    if(!is_dir($dir)&&!mkdir($dir,0700,true)&&!is_dir($dir))throw new RuntimeException('Medienordner nicht verfügbar.');
    if(is_link($dir)||!is_writable($dir))throw new RuntimeException('Medienordner nicht verfügbar.');
    return $dir;
}
function thv_media_url(string $id): string {return 'website-media.php?id='.$id;}
function thv_media_choices(array $s): array {$out=[];foreach($s['media']??[] as $id=>$m)if(preg_match('/^[a-f0-9]{24}$/',(string)$id))$out[thv_media_url($id)]=$m['name'];return $out;}
function thv_media_public(array $s,string $id): bool {
    $url=thv_media_url($id);
    foreach($s['entries'] as $e)if($e['visibility']==='public'&&($e['image']??'')===$url)return true;
    foreach($s['studio']['published']??[] as $p){if(in_array($url,$p['fields']??[],true))return true;foreach($p['trainers']??[] as $t)if(($t['image']??'')===$url)return true;foreach($p['blocks']??[] as $b){if(($b['image']??'')===$url)return true;foreach($b['elements']??[] as $e)if(($e['image']??'')===$url)return true;}}
    return false;
}
