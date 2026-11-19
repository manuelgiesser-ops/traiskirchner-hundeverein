<?php
declare(strict_types=1);
require __DIR__.'/verein-core.php';
require __DIR__.'/studio-blocks-lib.php';
thv_headers();thv_session();
$error='';$ok='';$ready=false;
function backup_check_state(array $s): void {
    foreach(['users','entries','requests','attempts'] as $k)if(!isset($s[$k])||!is_array($s[$k]))throw new DomainException('Ungültige Sicherungsdatei.');
    if(count($s['users'])>5000||count($s['entries'])>10000||count($s['requests'])>30000)throw new DomainException('Sicherung zu groß.');
    $admin=false;$emails=[];
    foreach($s['users'] as $id=>$u){
        if(!is_array($u)||!preg_match('/^[a-f0-9]{24}$/',(string)$id)||($u['id']??null)!==$id||!is_string($u['name']??null)||!filter_var($u['email']??'',FILTER_VALIDATE_EMAIL)||!is_string($u['hash']??null)||!in_array($u['role']??null,['member','editor','admin'],true)||!is_bool($u['active']??null)||!is_bool($u['change']??null)||!is_int($u['version']??null)||!is_string($u['created']??null)||strlen($u['hash'])>255||password_get_info($u['hash'])['algo']===null)throw new DomainException('Ungültiges Benutzerkonto.');
        if(isset($emails[$u['email']]))throw new DomainException('Doppelte E-Mail-Adresse.');$emails[$u['email']]=true;
        if($u['active']&&$u['role']==='admin'&&!$u['change'])$admin=true;
    }if(!$admin)throw new DomainException('Sicherung enthält keinen aktiven Administrator.');
    $validImage=fn($v)=>$v===''||(is_string($v)&&preg_match('~^(assets/[a-z0-9-]+\.webp|website-media\.php\?id=[a-f0-9]{24})$~',$v));
    foreach($s['entries'] as $id=>$e){if(!is_array($e)||!preg_match('/^[a-f0-9]{24}$/',(string)$id)||($e['id']??null)!==$id||!in_array($e['type']??null,['post','course'],true)||!in_array($e['visibility']??null,['draft','public','members'],true)||!$validImage($e['image']??null))throw new DomainException('Ungültiger Beitrag.');foreach(['title','summary','body','date','age','schedule','updated','created','author'] as $k)if(!is_string($e[$k]??null)||strlen($e[$k])>30000)throw new DomainException('Ungültiger Beitrag.');}
    foreach($s['requests'] as $id=>$q){if(!is_array($q)||!preg_match('/^[a-f0-9]{24}$/',(string)$id)||($q['id']??null)!==$id||!isset($s['users'][$q['user']??''],$s['entries'][$q['course']??''])||!in_array($q['status']??null,['pending','confirmed','waitlist','cancelled'],true)||!is_string($q['note']??null)||!is_string($q['created']??null))throw new DomainException('Ungültige Kursanfrage.');}
    if(isset($s['media'])&&!is_array($s['media']))throw new DomainException('Ungültiges Medienarchiv.');
    if(count($s['media']??[])>200)throw new DomainException('Zu viele Medien.');
    foreach($s['media']??[] as $id=>$m)if(!preg_match('/^[a-f0-9]{24}$/',(string)$id)||!is_array($m)||!is_string($m['name']??null)||!is_int($m['width']??null)||!is_int($m['height']??null)||!is_int($m['bytes']??null)||$m['bytes']<1||$m['bytes']>3000000||$m['width']<1||$m['width']>1600||$m['height']<1||$m['height']>1600)throw new DomainException('Ungültiges Medium.');
    // Validate every studio revision against the same allowlist, including image references.
    if(isset($s['studio'])){
        $manifest=json_decode((string)file_get_contents(__DIR__.'/studio-manifest.json'),true,512,JSON_THROW_ON_ERROR);
        $st=$s['studio'];if(!is_array($st)||!is_int($st['revision']??null)||!is_array($st['history']??null)||count($st['history'])>20)throw new DomainException('Ungültige Studio-Historie.');
        $check=function($content)use($manifest,$s){if(!is_array($content))throw new DomainException('Ungültiger Seitenentwurf.');foreach($content as $page=>$p){if(!isset($manifest[$page])||!is_array($p)||!is_array($p['fields']??null)||!is_array($p['hidden']??null)||!is_array($p['order']??null))throw new DomainException('Die Studio-Version passt nicht zur Website.');if(isset($p['blocks'])){$allowedMedia=thv_media_choices($s);foreach(glob(__DIR__.'/assets/*-960.webp')?:[] as $file)$allowedMedia['assets/'.basename($file)]=true;thv_blocks($p['blocks'],$allowedMedia);}if(isset($p['trainers'])){if($page!=='team.html'||!is_array($p['trainers'])||count($p['trainers'])>60)throw new DomainException('Ungültiges Trainerteam.');foreach($p['trainers'] as $t){if(!is_array($t)||!is_string($t['id']??null))throw new DomainException('Ungültiger Trainer.');if(!isset($manifest[$page]['trainers'][$t['id']])){if(!preg_match('/^new-[a-f0-9]{16}$/',$t['id'])||!is_string($t['name']??null)||!is_string($t['role']??null)||!is_string($t['image']??null))throw new DomainException('Ungültiger Trainer.');$im=$t['image'];if(preg_match('~^website-media\.php\?id=([a-f0-9]{24})$~',$im,$x)){if(!isset($s['media'][$x[1]]))throw new DomainException('Bild fehlt.');}elseif(!preg_match('~^assets/[a-z0-9-]+-960\.webp$~',$im)||!is_file(__DIR__.'/'.$im))throw new DomainException('Bild fehlt.');}}}if(isset($p['format'])){if(!is_array($p['format']))throw new DomainException('Ungültiges Format.');foreach($p['format'] as $k=>$f){if(!isset($manifest[$page]['fields'][$k])||$manifest[$page]['fields'][$k]['kind']==='image'||!is_array($f)||!in_array($f['font']??'default',['default','sans','modern','serif','humanist'],true)||!in_array($f['size']??'default',['default','small','medium','large','xl'],true)||!in_array($f['weight']??'default',['default','regular','bold'],true))throw new DomainException('Ungültiges Format.');foreach(['align'=>['default','left','center','right'],'line'=>['default','compact','normal','wide'],'style'=>['default','italic','normal'],'color'=>['default','green','dark','gold','cream']] as $prop=>$allowed)if(!in_array($f[$prop]??'default',$allowed,true))throw new DomainException('Ungültiges Format.');}}foreach($p['fields'] as $k=>$v){$d=$manifest[$page]['fields'][$k]??null;if(!$d||!is_string($v)||strlen($v)>12000||!preg_match('//u',$v))throw new DomainException('Ungültiges Inhaltsfeld.');if($d['kind']==='image'){if(preg_match('~^website-media\.php\?id=([a-f0-9]{24})$~',$v,$m)){if(!isset($s['media'][$m[1]]))throw new DomainException('Bild fehlt.');}elseif(!preg_match('~^assets/[a-z0-9-]+-960\.webp$~',$v)||!is_file(__DIR__.'/'.$v))throw new DomainException('Bild fehlt.');}}$keys=array_keys($manifest[$page]['sections']);foreach(array_merge($p['hidden'],$p['order']) as $k)if(!is_string($k)||!in_array($k,$keys,true))throw new DomainException('Ungültige Abschnitte.');}};
        $check($st['published']??[]);$check($st['draft']??[]);foreach($st['history'] as $v){if(!is_string($v['date']??null)||!is_string($v['author']??null)||!preg_match('/^[a-f0-9]{24}$/',(string)($v['id']??'')))throw new DomainException('Ungültige Version.');$check($v['content']??null);}
    }
}
try{
    $state=thv_state(fn($s)=>$s);$u=thv_user($state);thv_need($u,'admin');if(!thv_secure())throw new DomainException('HTTPS erforderlich.');$ready=true;
    if($_SERVER['REQUEST_METHOD']==='POST'){
        thv_check();
        if(!password_verify((string)($_POST['password']??''),$u['hash']))throw new DomainException('Passwort stimmt nicht.');
        if(!class_exists('ZipArchive'))throw new DomainException('PHP ZIP muss am Hosting aktiviert sein.');
        $action=(string)($_POST['action']??'');
        if($action==='export'){
            $tmp=tempnam(thv_private(),'backup-');if(!$tmp)throw new RuntimeException('Sicherung fehlgeschlagen.');
            try{
                $zip=new ZipArchive();if($zip->open($tmp,ZipArchive::OVERWRITE)!==true)throw new RuntimeException('ZIP fehlgeschlagen.');
                $snapshot=$state;$snapshot['attempts']=[];
                $zip->addFromString('backup.json',json_encode(['format'=>'THV-1','created'=>date(DATE_ATOM),'state'=>$snapshot],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
                foreach($snapshot['media']??[] as $id=>$m){$p=thv_media_dir().'/'.$id.'.webp';if(!is_file($p)||is_link($p)||!$zip->addFile($p,'media/'.$id.'.webp'))throw new RuntimeException('Medium fehlt.');}
                if(!$zip->close())throw new RuntimeException('ZIP fehlgeschlagen.');
                header('Content-Type: application/zip');header('Content-Disposition: attachment; filename="THV-Sicherung-'.date('Y-m-d-His').'.zip"');header('Content-Length: '.filesize($tmp));readfile($tmp);unlink($tmp);exit;
            }finally{if(is_file($tmp))unlink($tmp);}
        }
        if($action==='restore'){
            if(($_POST['confirm']??'')!=='WIEDERHERSTELLEN')throw new DomainException('Bitte WIEDERHERSTELLEN eingeben.');
            $f=$_FILES['backup']??null;if(!$f||$f['error']!==UPLOAD_ERR_OK||!is_uploaded_file($f['tmp_name'])||$f['size']>85000000)throw new DomainException('Bitte eine gültige ZIP-Sicherung auswählen.');
            $zip=new ZipArchive();if($zip->open($f['tmp_name'],ZipArchive::RDONLY)!==true)throw new DomainException('ZIP konnte nicht geöffnet werden.');
            $written=[];
            try{
                $info=$zip->statName('backup.json');if(!$info||$info['size']>20000000)throw new DomainException('Sicherungsdatei ist ungültig.');
                $payload=json_decode($zip->getFromName('backup.json'),true,512,JSON_THROW_ON_ERROR);
                if(($payload['format']??null)!=='THV-1'||!is_array($payload['state']??null))throw new DomainException('Unbekanntes Sicherungsformat.');
                $restored=$payload['state'];backup_check_state($restored);$restored['attempts']=[];
                $sum=0;foreach($restored['media']??[] as $id=>$m){
                    $name='media/'.$id.'.webp';$i=$zip->statName($name);$sum+=$m['bytes'];
                    if(!$i||$i['size']!==$m['bytes']||$sum>60000000)throw new DomainException('Medienarchiv ungültig.');
                    $bytes=$zip->getFromName($name);$im=@getimagesizefromstring($bytes);
                    if(!$im||$im[2]!==IMAGETYPE_WEBP||$im[0]!==$m['width']||$im[1]!==$m['height'])throw new DomainException('Ungültiges WebP-Bild.');
                    $path=thv_media_dir().'/'.$id.'.webp';if(is_file($path)){if(is_link($path)||!hash_equals(hash_file('sha256',$path),hash('sha256',$bytes)))throw new DomainException('Ein vorhandenes Bild hat dieselbe ID, aber einen anderen Inhalt.');}
                    else{if(file_put_contents($path,$bytes,LOCK_EX)!==strlen($bytes))throw new RuntimeException('Medium konnte nicht gespeichert werden.');chmod($path,0600);$written[]=$path;}
                }
                thv_state(function(&$s)use($restored){thv_need(thv_user($s),'admin');$safety=thv_private().'/vor-wiederherstellung-'.date('Ymd-His').'-'.thv_id().'.json';$json=json_encode($s,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);if(file_put_contents($safety,$json)!==strlen($json))throw new RuntimeException('Sicherheitskopie fehlgeschlagen.');chmod($safety,0600);$s=$restored;foreach($s['users'] as &$account)$account['version']++;unset($account);},true);
            }catch(Throwable $e){foreach($written as $p)@unlink($p);throw $e;}finally{$zip->close();}
            $_SESSION=[];session_regenerate_id(true);thv_flash('Sicherung wiederhergestellt. Melde dich mit einem Konto aus der Sicherung an.');thv_redirect();
        }
        throw new DomainException('Unbekannte Aktion.');
    }
}catch(DomainException $e){$error=$e->getMessage();}catch(Throwable $e){error_log('THV backup: '.$e->getMessage());$error='Datensicherung momentan nicht verfügbar.';}
if(!$ready){header('Location: vereinsbereich.php',true,303);exit;}
thv_layout_start('Datensicherung');
?><div class="club-heading"><div><h1>Datensicherung</h1><p>Den Verein sichern und auf ein anderes Hosting mitnehmen.</p></div><a class="secondary" href="vereinsbereich.php">Zur Übersicht</a></div><?php if($error):?><p class="club-message error" role="alert"><?=h($error)?></p><?php endif;?><section class="club-card"><h2>Vollständige Sicherung exportieren</h2><p>Die ZIP-Datei umfasst Mitgliederkonten mit Passwortprüfwerten, Nachrichten, Kursanfragen, Studio-Inhalte samt Versionen und eigene hochgeladene Bilder. Bewahre sie verschlüsselt und vertraulich auf.</p><form method="post" class="club-form"><?php thv_action('export');thv_field('Dein aktuelles Passwort','password','password');?><button>Sicherung herunterladen</button></form></section><section class="club-card"><h2>Sicherung wiederherstellen</h2><p>Die Wiederherstellung ersetzt alle bisherigen Vereinsdaten. Melde dich anschließend mit einem Administratorkonto aus der Sicherung an. Vorher wird eine private Sicherheitskopie des bisherigen Datenstands erstellt.</p><form method="post" enctype="multipart/form-data" class="club-form"><?php thv_action('restore');?><label>ZIP-Sicherung<input type="file" name="backup" accept=".zip,application/zip" required></label><?php thv_field('Dein aktuelles Passwort','password','password');thv_field('Zur Bestätigung WIEDERHERSTELLEN eingeben','confirm');?><button>Sicherung einspielen</button></form></section><section class="club-card"><h2>Hostingwechsel</h2><p>Die Website-Dateien aus GitHub auf das neue Hosting kopieren, dort den privaten Ordner und den Vereinsbereich einrichten und anschließend die ZIP-Sicherung einspielen. Die bisherigen Konten und Inhalte werden übernommen. Bei großen Medienarchiven kann alternativ der private Ordner vollständig übertragen werden.</p></section><?php thv_layout_end();?>
