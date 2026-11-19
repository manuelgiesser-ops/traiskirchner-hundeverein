<?php
declare(strict_types=1);
function studio_grid_elements($elements,array $allowedMedia): array {
 if(!is_array($elements)||count($elements)>24)throw new DomainException('Maximal 24 Elemente pro Layout-Abschnitt.');
 $out=[];$seen=[];$occupied=['desktop'=>[],'mobile'=>[]];
 foreach($elements as $e){
  if(!is_array($e)||!is_string($e['id']??null)||!preg_match('/^el-[a-f0-9]{16}$/D',$e['id'])||isset($seen[$e['id']])||!in_array($e['kind']??'', ['heading','text','image','button','divider','spacer'],true))throw new DomainException('Ungültiges Layout-Element.');
  $seen[$e['id']]=true;$x=['id'=>$e['id'],'kind'=>$e['kind']];
  foreach(['text'=>16000,'image'=>240,'alt'=>960,'link'=>400] as $k=>$limit){$str=$e[$k]??'';if(!is_string($str)||strlen($str)>$limit||!preg_match('//u',$str))throw new DomainException('Ungültiger Elementinhalt.');$x[$k]=$str;}
  if($x['image']!==''&&!isset($allowedMedia[$x['image']]))throw new DomainException('Bild nicht verfügbar.');
  if($x['link']!==''&&!preg_match('~^(?:[a-zA-Z0-9_-]+\.html(?:#[a-zA-Z0-9_-]+)?|https://[^\s<>"\\\\]+)$~D',$x['link']))throw new DomainException('Bitte einen gültigen Seitenlink oder HTTPS-Link eingeben.');
  $height=$e['height']??32;if(!is_int($height)||$height<16||$height>160||$height%8!==0)throw new DomainException('Ungültiger Abstand.');$x['height']=$height;
  $f=$e['format']??[];if(!is_array($f))throw new DomainException('Ungültiges Textformat.');$x['format']=[];
  foreach(['font'=>['default','sans','modern','serif','humanist'],'weight'=>['default','regular','bold'],'style'=>['default','normal','italic'],'align'=>['default','left','center','right'],'color'=>['default','green','dark','gold','cream']] as $k=>$a){if(isset($f[$k])){if(!in_array($f[$k],$a,true))throw new DomainException('Ungültiges Textformat.');$x['format'][$k]=$f[$k];}}
  if(isset($f['px'])){if(!is_int($f['px'])||$f['px']<12||$f['px']>96)throw new DomainException('Ungültige Schriftgröße.');$x['format']['px']=$f['px'];}
  $x['layout']=[];foreach(['desktop','mobile'] as $mode){$p=$e['layout'][$mode]??null;if(!is_array($p)||!is_int($p['row']??null)||!is_int($p['col']??null)||!is_int($p['span']??null)||$p['row']<1||$p['row']>64||$p['col']<1||$p['span']<($mode==='mobile'?4:3)||$p['col']+$p['span']>13)throw new DomainException('Ungültige Rasterposition.');
   for($i=$p['col'];$i<$p['col']+$p['span'];$i++){if(isset($occupied[$mode][$p['row']][$i]))throw new DomainException('Elemente überlappen sich.');$occupied[$mode][$p['row']][$i]=true;}
   $x['layout'][$mode]=['row'=>$p['row'],'col'=>$p['col'],'span'=>$p['span']];
  }$out[]=$x;
 }return $out;
}
function thv_blocks($blocks,array $allowedMedia,array $sections=[]): array {
    if(!is_array($blocks)||count($blocks)>40)throw new DomainException('Maximal 40 zusätzliche Abschnitte pro Seite.');
    $out=[];$ids=[];
    foreach($blocks as $b){
        if(!is_array($b)||!is_string($b['id']??null)||!preg_match('/^block-[a-f0-9]{16}$/',$b['id'])||isset($ids[$b['id']])||!in_array($b['type']??null,['grid','text','image','photo','notice','cta','faq','list','quote'],true))throw new DomainException('Ungültiger Baustein.');
        $ids[$b['id']]=true;$x=['id'=>$b['id'],'type'=>$b['type']];
        foreach(['title'=>240,'body'=>12000,'button'=>120,'link'=>400,'image'=>100,'alt'=>240] as $k=>$max){$v=$b[$k]??'';if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Bitte prüfe den Bausteininhalt.');$x[$k]=trim($v);}
        if($x['title']==='')throw new DomainException('Bitte einen Bausteintitel eingeben.');
        if($x['image']!==''&&!isset($allowedMedia[$x['image']]))throw new DomainException('Dieses Bild ist nicht verfügbar.');
        if($x['link']!==''&&!preg_match('~^(?:[a-z0-9-]+\.html(?:#[a-zA-Z0-9_-]+)?|https://[^\s<>"\\\\]+)$~D',$x['link']))throw new DomainException('Bitte einen internen Seitenlink oder eine HTTPS-Adresse verwenden.');
        if(!in_array($b['tone']??'light',['light','cream','green'],true))throw new DomainException('Ungültiger Farbstil.');
        $x['tone']=$b['tone']??'light';if(isset($b['typography'])){if(!is_array($b['typography']))throw new DomainException('Ungültige Schriftgestaltung.');$x['typography']=[];foreach($b['typography'] as $part=>$fmt){if(!in_array($part,['title','body'],true)||!is_array($fmt))throw new DomainException('Ungültige Schriftgestaltung.');$clean=[];foreach(['font'=>['default','sans','modern','serif','humanist'],'weight'=>['default','regular','bold'],'style'=>['default','normal','italic'],'align'=>['default','left','center','right'],'color'=>['default','green','dark','gold','cream']] as $key=>$allowed){if(isset($fmt[$key])){if(!in_array($fmt[$key],$allowed,true))throw new DomainException('Ungültige Schriftgestaltung.');$clean[$key]=$fmt[$key];}}if(isset($fmt['px'])){if(!is_int($fmt['px'])||$fmt['px']<12||$fmt['px']>96)throw new DomainException('Ungültige Schriftgröße.');$clean['px']=$fmt['px'];}$x['typography'][$part]=$clean;}}$position=$b['placement']??[];if(!is_array($position))throw new DomainException('Ungültige Position.');$x['placement']=[];foreach(['desktop','mobile'] as $device){$pos=$position[$device]??[];if(!is_array($pos))throw new DomainException('Ungültige Geräteposition.');$width=$pos['width']??100;$px=$pos['x']??0;$top=$pos['top']??0;if(!is_int($width)||!is_int($px)||!is_int($top)||$width<35||$width>100||$width%5!==0||$px<0||$px>100-$width||$px%5!==0||$top<0||$top>160||$top%8!==0)throw new DomainException('Position außerhalb des Layouts.');$x['placement'][$device]=['width'=>$width,'x'=>$px,'top'=>$top];}$anchor=$b['anchor']??'end';$spacing=$b['spacing']??'normal';$side=$b['side']??'left';if(!is_string($anchor)||!in_array($anchor,array_merge(['end'],$sections),true)||!in_array($spacing,['compact','normal','wide'],true)||!in_array($side,['left','right'],true))throw new DomainException('Ungültige Position oder Darstellung.');$x['anchor']=$anchor;$x['spacing']=$spacing;$x['side']=$side;if($x['type']==='grid')$x['elements']=studio_grid_elements($b['elements']??[],$allowedMedia);$out[]=$x;
    }return $out;
}
