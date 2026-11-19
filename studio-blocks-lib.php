<?php
declare(strict_types=1);
function thv_blocks($blocks,array $allowedMedia,array $sections=[]): array {
    if(!is_array($blocks)||count($blocks)>20)throw new DomainException('Maximal 20 zusätzliche Bausteine pro Seite.');
    $out=[];$ids=[];
    foreach($blocks as $b){
        if(!is_array($b)||!is_string($b['id']??null)||!preg_match('/^block-[a-f0-9]{16}$/',$b['id'])||isset($ids[$b['id']])||!in_array($b['type']??null,['text','image','photo','notice','cta','faq','list','quote'],true))throw new DomainException('Ungültiger Baustein.');
        $ids[$b['id']]=true;$x=['id'=>$b['id'],'type'=>$b['type']];
        foreach(['title'=>240,'body'=>12000,'button'=>120,'link'=>400,'image'=>100,'alt'=>240] as $k=>$max){$v=$b[$k]??'';if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Bitte prüfe den Bausteininhalt.');$x[$k]=trim($v);}
        if($x['title']==='')throw new DomainException('Bitte einen Bausteintitel eingeben.');
        if($x['image']!==''&&!isset($allowedMedia[$x['image']]))throw new DomainException('Dieses Bild ist nicht verfügbar.');
        if($x['link']!==''&&!preg_match('~^(?:[a-z0-9-]+\.html(?:#[a-zA-Z0-9_-]+)?|https://[^\s<>"\\\\]+)$~D',$x['link']))throw new DomainException('Bitte einen internen Seitenlink oder eine HTTPS-Adresse verwenden.');
        if(!in_array($b['tone']??'light',['light','cream','green'],true))throw new DomainException('Ungültiger Farbstil.');
        $x['tone']=$b['tone']??'light';$position=$b['placement']??[];if(!is_array($position))throw new DomainException('Ungültige Position.');$x['placement']=[];foreach(['desktop','mobile'] as $device){$pos=$position[$device]??[];if(!is_array($pos))throw new DomainException('Ungültige Geräteposition.');$width=$pos['width']??100;$px=$pos['x']??0;$top=$pos['top']??0;if(!is_int($width)||!is_int($px)||!is_int($top)||$width<35||$width>100||$width%5!==0||$px<0||$px>100-$width||$px%5!==0||$top<0||$top>160||$top%8!==0)throw new DomainException('Position außerhalb des Layouts.');$x['placement'][$device]=['width'=>$width,'x'=>$px,'top'=>$top];}$anchor=$b['anchor']??'end';$spacing=$b['spacing']??'normal';$side=$b['side']??'left';if(!is_string($anchor)||!in_array($anchor,array_merge(['end'],$sections),true)||!in_array($spacing,['compact','normal','wide'],true)||!in_array($side,['left','right'],true))throw new DomainException('Ungültige Position oder Darstellung.');$x['anchor']=$anchor;$x['spacing']=$spacing;$x['side']=$side;$out[]=$x;
    }return $out;
}
