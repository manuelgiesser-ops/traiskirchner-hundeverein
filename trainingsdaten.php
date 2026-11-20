<?php
declare(strict_types=1);require __DIR__.'/verein-core.php';require __DIR__.'/thv-community-lib.php';header('X-Content-Type-Options: nosniff');header('Cache-Control: no-cache');
try{$events=thv_state(fn($s)=>thv_events($s));
 if(isset($_GET['ics'])){$event=null;foreach($events as $e)if($e['id']===$_GET['ics'])$event=$e;if(!$event||$event['status']==='cancelled'){http_response_code(404);exit;}
 $esc=fn($s)=>str_replace(["\\","\r\n","\n","\r",";",","],["\\\\","\\n","\\n","\\n","\\;","\\,"],$s);$d=str_replace('-','',$event['date']);
 $start=$event['time']?'DTSTART:'.(new DateTimeImmutable($event['date'].' '.$event['time'],new DateTimeZone('Europe/Vienna')))->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'):'DTSTART;VALUE=DATE:'.$d;
 $end=$event['end']?'DTEND:'.(new DateTimeImmutable($event['date'].' '.$event['end'],new DateTimeZone('Europe/Vienna')))->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z'):'';
 header('Content-Type: text/calendar; charset=utf-8');header('Content-Disposition: attachment; filename="Hundeverein-Termin.ics"');echo implode("\r\n",array_filter(['BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//THV//Training//DE','BEGIN:VEVENT','UID:'.$event['id'].'@traiskirchner-hundeverein.at','DTSTAMP:'.gmdate('Ymd\THis\Z'),$start,$end,'SUMMARY:'.$esc($event['title']),'DESCRIPTION:'.$esc($event['note']),'END:VEVENT','END:VCALENDAR']))."\r\n";exit;}
 header('Content-Type: application/json; charset=utf-8');echo json_encode(['events'=>$events],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){header('Content-Type: application/json; charset=utf-8');http_response_code(503);echo '{"events":[]}';}
