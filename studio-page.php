<?php
declare(strict_types=1);
require __DIR__.'/studio-bootstrap.php';
require __DIR__.'/studio-seo-lib.php';
$page=$_GET['page']??'';
if(!is_string($page)||!preg_match('/^[a-zA-Z0-9_-]+\.html$/D',$page)||!is_file(__DIR__.'/'.$page)){http_response_code(404);exit('Seite nicht gefunden.');}
$html=file_get_contents(__DIR__.'/'.$page);
if($html===false){http_response_code(503);exit;}
header('Content-Type: text/html; charset=utf-8');header('Cache-Control: no-cache');header('X-Content-Type-Options: nosniff');
if(str_contains(parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH)??'','studio-page.php'))header('X-Robots-Tag: noindex');
try{$seo=thv_state(fn($s)=>$s['studio']['published'][$page]['seo']??[]);if($seo)$html=studio_seo_render($html,studio_seo_validate($seo));}catch(Throwable $e){error_log('Studio SEO fallback');}
echo $html;
