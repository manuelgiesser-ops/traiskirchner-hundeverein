<?php
declare(strict_types=1);
function thv_blocks($blocks,array $allowedMedia): array {
    if(!is_array($blocks)||count($blocks)>20)throw new DomainException('Maximal 20 zusätzliche Bausteine pro Seite.');
    $out=[];$ids=[];
    foreach($blocks as $b){
        if(!is_array($b)||!is_string($b['id']??null)||!preg_match('/^block-[a-f0-9]{16}$/',$b['id'])||isset($ids[$b['id']])||!in_array($b['type']??null,['text','image','notice','cta','faq'],true))throw new DomainException('Ungültiger Baustein.');
        $ids[$b['id']]=true;$x=['id'=>$b['id'],'type'=>$b['type']];
        foreach(['title'=>240,'body'=>12000,'button'=>120,'link'=>400,'image'=>100,'alt'=>240] as $k=>$max){$v=$b[$k]??'';if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Bitte prüfe den Bausteininhalt.');$x[$k]=trim($v);}
        if($x['title']==='')throw new DomainException('Bitte einen Bausteintitel eingeben.');
        if($x['image']!==''&&!isset($allowedMedia[$x['image']]))throw new DomainException('Dieses Bild ist nicht verfügbar.');
        if($x['link']!==''&&!preg_match('~^(?:[a-z0-9-]+\.html(?:#[a-zA-Z0-9_-]+)?|https://[^\s<>"\\\\]+)$~D',$x['link']))throw new DomainException('Bitte einen internen Seitenlink oder eine HTTPS-Adresse verwenden.');
        if(!in_array($b['tone']??'light',['light','cream','green'],true))throw new DomainException('Ungültiger Farbstil.');
        $x['tone']=$b['tone']??'light';$out[]=$x;
    }return $out;
}
