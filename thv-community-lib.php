<?php
declare(strict_types=1);
function thv_courses(array $s):array {
 $c=['beratung'=>'Noch unsicher / Beratung','welpen'=>'Welpen und Junghunde','grundkurs'=>'Grundkurs','unterordnung'=>'Unterordnung','fortgeschritten'=>'Unterordnung Fortgeschrittene','agility'=>'Agility','hoopers'=>'Hoopers','social-walk'=>'Social Walk','kontakt'=>'Allgemeine Frage'];
 foreach($s['entries'] as $id=>$e)if($e['type']==='course'&&$e['visibility']==='public')$c[$id]=$e['title'];return $c;
}
function thv_training_status():array{return ['scheduled'=>'Findet statt','full'=>'Ausgebucht','cancelled'=>'Abgesagt','changed'=>'Geändert'];}
function thv_day(string $s):bool{return preg_match('/^\d{4}-\d{2}-\d{2}$/D',$s)&&checkdate((int)substr($s,5,2),(int)substr($s,8,2),(int)substr($s,0,4));}
function thv_events(array $s):array{
 $out=[];foreach($s['training_dates']??[] as $e)if($e['date']>=date('Y-m-d')&&$e['date']<=date('Y-m-d',strtotime('+90 days')))$out[]=$e;
 foreach($s['entries'] as $e)if(in_array($e['type'],['post','event','tournament'],true)&&$e['visibility']==='public'&&$e['date']>=date('Y-m-d')&&$e['date']<=date('Y-m-d',strtotime('+90 days')))$out[]=['id'=>$e['id'],'course'=>'event','title'=>$e['title'],'date'=>$e['date'],'time'=>$e['time']??'','end'=>$e['end']??'','status'=>'scheduled','note'=>trim(($e['summary']??'').(!empty($e['place'])?' · Ort: '.$e['place']:'')),'type'=>$e['type'],'link'=>'vereinsartikel.php?id='.$e['id']];
 usort($out,fn($a,$b)=>strcmp($a['date'].$a['time'],$b['date'].$b['time']));return $out;
}
function thv_inquiry(array $input,array $courses):array{
 $q=[];foreach(['name'=>120,'email'=>254,'phone'=>60,'dog'=>100,'age'=>60,'breed'=>100,'note'=>2000,'course'=>24,'kind'=>20] as $k=>$max){$v=$input[$k]??'';if(!is_string($v)||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Bitte prüfe deine Angaben.');$q[$k]=trim($v);}
 if($q['name']===''||!filter_var($q['email'],FILTER_VALIDATE_EMAIL)||!in_array($q['kind'],['course','trial','contact'],true))throw new DomainException('Bitte Namen, E-Mail-Adresse und Anliegen angeben.');
 if($q['kind']==='contact'){if(!$q['note'])throw new DomainException('Bitte schreibe uns deine Nachricht.');$q['course']='kontakt';$q['dog']=$q['age']=$q['breed']='';}
 elseif(!isset($courses[$q['course']])||$q['course']==='kontakt'||!$q['dog']||!$q['age'])throw new DomainException('Bitte Kurs, Name und Alter des Hundes angeben.');
 $q['title']=$courses[$q['course']];return $q;
}
function thv_inquiry_notify(array $q):array{
 $r=['office'=>false,'confirmation'=>false];$p=thv_private().'/mail-settings.json';if(!is_file($p)||is_link($p))return $r;$cfg=json_decode((string)file_get_contents($p),true);
 if(!is_array($cfg)||!filter_var($cfg['from']??'',FILTER_VALIDATE_EMAIL)||!filter_var($cfg['office']??'',FILTER_VALIDATE_EMAIL))return $r;
 $office=$cfg['office'];$target=$cfg['trainers'][$q['course']]??$office;if(!filter_var($target,FILTER_VALIDATE_EMAIL))$target=$office;
 $headers=['From'=>$cfg['from'],'Reply-To'=>$office,'Content-Type'=>'text/plain; charset=UTF-8'];
 try{$r['office']=@mail($target,'Neue Anfrage beim Hundeverein',"Neue Anfrage: ".$q['title']."\nBitte im geschützten Vereinsbereich öffnen.\nReferenz: ".$q['id'],$headers);$r['confirmation']=@mail($q['email'],'Deine Anfrage beim Hundeverein',"Hallo ".$q['name']."!\n\nDeine Anfrage ist angekommen. Wir melden uns bei dir. Eine Teilnahme ist noch nicht bestätigt.\n\nTraiskirchner Hundeverein",$headers);}catch(Throwable $e){error_log('THV inquiry notification failed');}return $r;
}
