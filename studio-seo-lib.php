<?php
declare(strict_types=1);
function studio_seo_validate($input): array {
 if(!is_array($input))throw new DomainException('Ungültige SEO-Daten.');
 $out=[];foreach(['title'=>480,'description'=>1280] as $key=>$max){if(!array_key_exists($key,$input))continue;$v=$input[$key];if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Ungültiger SEO-Text.');$out[$key]=trim($v);}return $out;
}
function studio_seo_defaults(string $page): array {
 if(!preg_match('/^[a-zA-Z0-9_-]+\.html$/D',$page))return [];
 $html=@file_get_contents(__DIR__.'/'.$page);if(!is_string($html))return [];
 preg_match('~<head\b[^>]*>(.*?)</head\s*>~is',$html,$head);$head=$head[1]??'';
 preg_match('~<title\b[^>]*>(.*?)</title\s*>~is',$head,$title);
 preg_match('~<meta\s+[^>]*name=["\']description["\'][^>]*content=(["\'])(.*?)\1[^>]*>~is',$head,$desc);
 return ['title'=>html_entity_decode(strip_tags($title[1]??''),ENT_QUOTES|ENT_HTML5,'UTF-8'),'description'=>html_entity_decode($desc[2]??'',ENT_QUOTES|ENT_HTML5,'UTF-8')];
}
function studio_seo_render(string $html,array $seo): string {
 return preg_replace_callback('~<head\b[^>]*>.*?</head\s*>~is',function($m)use($seo){$head=$m[0];$escape=fn($v)=>htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$add='';
 if(isset($seo['title'])){$v=$escape($seo['title']);$tag='<title>'.$v.'</title>';$head=preg_replace_callback('~<title\b[^>]*>.*?</title\s*>~is',fn()=>$tag,$head,-1,$count);if(!$count)$add.=$tag;}
 foreach(['description'=>['name','description'],'og:title'=>['property','og:title'],'og:description'=>['property','og:description']] as $key=>[$attr,$name]){$source=$key==='og:title'?'title':'description';if(!isset($seo[$source]))continue;$tag='<meta '.$attr.'="'.$name.'" content="'.$escape($seo[$source]).'">';$pattern='~<meta\b(?=[^>]*\b'.$attr.'\s*=\s*["\']'.preg_quote($name,'~').'["\'])[^>]*>~i';$head=preg_replace_callback($pattern,fn()=>$tag,$head,-1,$count);if(!$count)$add.=$tag;}
 return preg_replace_callback('~</head\s*>~i',fn()=>$add.'</head>',$head,1);
 },$html,1)??$html;
}
