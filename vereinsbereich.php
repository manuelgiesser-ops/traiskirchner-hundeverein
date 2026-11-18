<?php
declare(strict_types=1);
require __DIR__.'/verein-core.php';
thv_headers(); thv_session();
$error=''; $state=thv_empty(); $user=null; $ready=false; $secure=thv_secure();
try {
    $state=thv_state(fn($s)=>$s); $ready=true;
    $user=thv_user($state);
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if(!$secure)throw new DomainException('Bitte öffne den Vereinsbereich über HTTPS.');
        thv_check(); $action=(string)($_POST['action']??'');
        if($action==='setup'){
            $email=thv_email();$name=thv_text('name',120);$pass=thv_password();$key=thv_text('key',256);
            $u=thv_state(function(&$s)use($email,$name,$pass,$key){
                $file=thv_private().'/setup.key'; if($s['users']||!is_file($file)||is_link($file))return null;
                if(!thv_rate($s,'setup:'.($_SERVER['REMOTE_ADDR']??''),5))return null;
                $saved=trim((string)file_get_contents($file));if(strlen($saved)<32||!hash_equals($saved,$key))return null;
                $u=thv_new_user($email,$name,$pass,'admin',false);$s['users'][$u['id']]=$u;return $u;
            },true);
            if(!$u)throw new DomainException('Einrichtung nicht möglich. Bitte prüfe den Einrichtungsschlüssel.');
            @unlink(thv_private().'/setup.key');thv_login($u);thv_flash('Der Vereinsbereich ist eingerichtet.');thv_redirect();
        }
        if($action==='login'){
            $email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');
            $u=thv_state(function(&$s)use($email,$password){
                if(!thv_rate($s,'ip:'.($_SERVER['REMOTE_ADDR']??''),25)||!thv_rate($s,'login:'.$email,8))return null;
                foreach($s['users'] as $u)if($u['email']===$email&&$u['active']&&password_verify($password,$u['hash']))return $u;
                return null;
            },true);
            if(!$u)throw new DomainException('Anmeldung fehlgeschlagen. Bitte prüfe die Zugangsdaten oder versuche es später erneut.');
            thv_login($u);thv_redirect();
        }
        if($action==='logout'){$_SESSION=[];session_regenerate_id(true);thv_redirect();}
        if(!$user)throw new DomainException('Bitte melde dich zuerst an.');
        if($action==='password'){
            $old=(string)($_POST['old']??'');$new=thv_password();
            $u=thv_state(function(&$s)use($old,$new){$u=thv_user($s);if(!$u||!password_verify($old,$u['hash']))throw new DomainException('Das bisherige Passwort stimmt nicht.');
                $u['hash']=password_hash($new,PASSWORD_DEFAULT);$u['change']=false;$u['version']++;$s['users'][$u['id']]=$u;return $u;
            },true);
            thv_login($u);thv_flash('Passwort geändert.');thv_redirect();
        }
        if($action==='save-entry'){
            $id=thv_text('id',24,false);$type=thv_text('type',12);$visibility=thv_text('visibility',12);
            $title=thv_text('title',180);$summary=thv_text('summary',500);$body=thv_text('body',30000);
            $date=thv_text('date',10,false);$image=thv_text('image',100,false);$age=thv_text('age',100,false);$schedule=thv_text('schedule',250,false);
            if(!in_array($type,['post','course'],true)||!in_array($visibility,['draft','public','members'],true)||!array_key_exists($image,thv_images()))throw new DomainException('Bitte prüfe die Auswahl.');
            if($date&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4))))throw new DomainException('Bitte prüfe das Datum.');
            thv_state(function(&$s)use($id,$type,$visibility,$title,$summary,$body,$date,$image,$age,$schedule){
                $u=thv_user($s);thv_need($u,'admin','editor');
                if($id!==''&&!isset($s['entries'][$id]))throw new DomainException('Eintrag nicht gefunden.');
                $key=$id?:thv_id();$old=$s['entries'][$key]??null;
                if($old&&$old['type']!==$type)throw new DomainException('Die Art des Eintrags kann nicht geändert werden.');
                $s['entries'][$key]=['id'=>$key,'type'=>$type,'visibility'=>$visibility,'title'=>$title,'summary'=>$summary,'body'=>$body,'date'=>$date,'image'=>$image,'age'=>$age,'schedule'=>$schedule,'updated'=>date(DATE_ATOM),'created'=>$old['created']??date(DATE_ATOM),'author'=>$u['id']];
            },true);thv_flash('Eintrag gespeichert.');thv_redirect('redaktion');
        }
        if($action==='archive'){
            $id=thv_text('id',24);
            thv_state(function(&$s)use($id){thv_need(thv_user($s),'admin','editor');if(!isset($s['entries'][$id]))throw new DomainException('Eintrag nicht gefunden.');$s['entries'][$id]['visibility']='draft';$s['entries'][$id]['updated']=date(DATE_ATOM);},true);
            thv_flash('Eintrag ist wieder ein Entwurf.');thv_redirect('redaktion');
        }
        if($action==='create-user'){
            $name=thv_text('name',120);$email=thv_email();$role=thv_text('role',12);
            if(!in_array($role,['member','editor','admin'],true))throw new DomainException('Ungültige Rolle.');
            $pw=bin2hex(random_bytes(12));
            thv_state(function(&$s)use($name,$email,$role,$pw){thv_need(thv_user($s),'admin');if(thv_email_used($s,$email))throw new DomainException('Diese E-Mail-Adresse ist bereits vorhanden.');$u=thv_new_user($email,$name,$pw,$role,true);$s['users'][$u['id']]=$u;},true);
            thv_flash('Konto angelegt. Einmaliges Startpasswort für '.$email.': '.$pw.' Bitte persönlich weitergeben. Es muss beim ersten Login geändert werden.');thv_redirect('mitglieder');
        }
        if($action==='user-update'){
            $id=thv_text('id',24);$mode=thv_text('mode',12);$role=trim((string)($_POST['role']??''));$pw=bin2hex(random_bytes(12));
            $msg=thv_state(function(&$s)use($id,$mode,$role,$pw){$me=thv_user($s);thv_need($me,'admin');$u=$s['users'][$id]??null;
                if(!$u||$id===$me['id'])throw new DomainException('Das eigene Konto kann hier nicht geändert werden.');
                if($mode==='role'){if(!in_array($role,['member','editor','admin'],true))throw new DomainException('Ungültige Rolle.');$u['role']=$role;$msg='Rolle geändert.';}
                elseif($mode==='toggle'){$u['active']=!$u['active'];$msg=$u['active']?'Konto aktiviert.':'Konto deaktiviert.';}
                elseif($mode==='reset'){$u['hash']=password_hash($pw,PASSWORD_DEFAULT);$u['change']=true;$msg='Neues einmaliges Startpasswort für '.$u['email'].': '.$pw;}
                else throw new DomainException('Unbekannte Aktion.');
                $u['version']++;$s['users'][$id]=$u;return $msg;
            },true);thv_flash($msg);thv_redirect('mitglieder');
        }
        if($action==='request'){
            $id=thv_text('id',24);$note=thv_text('note',500,false);
            thv_state(function(&$s)use($id,$note){$u=thv_user($s);thv_need($u,'member','editor','admin');
                $e=$s['entries'][$id]??null;if(!$e||$e['type']!=='course'||!thv_visible($e,$u))throw new DomainException('Dieser Kurs ist nicht verfügbar.');
                foreach($s['requests'] as $q)if($q['user']===$u['id']&&$q['course']===$id&&$q['status']!=='cancelled')throw new DomainException('Du hast diesen Kurs bereits angefragt.');
                $key=thv_id();$s['requests'][$key]=['id'=>$key,'user'=>$u['id'],'course'=>$id,'note'=>$note,'status'=>'pending','created'=>date(DATE_ATOM)];
            },true);thv_flash('Deine Anfrage wurde gespeichert. Das Trainerteam entscheidet über die Teilnahme.');thv_redirect('anfragen');
        }
        if($action==='request-status'){
            $id=thv_text('id',24);$status=thv_text('status',15);
            thv_state(function(&$s)use($id,$status){$u=thv_user($s);thv_need($u,'member','editor','admin');$q=$s['requests'][$id]??null;if(!$q)throw new DomainException('Anfrage nicht gefunden.');
                if(thv_role($u,'admin','editor')){if(!in_array($status,['pending','confirmed','waitlist','cancelled'],true))throw new DomainException('Ungültiger Status.');}
                elseif($q['user']!==$u['id']||$status!=='cancelled'||$q['status']==='cancelled')throw new DomainException('Dafür fehlt die Berechtigung.');
                $s['requests'][$id]['status']=$status;
            },true);thv_flash('Anfrage aktualisiert.');thv_redirect('anfragen');
        }
        throw new DomainException('Unbekannte Aktion.');
    }
} catch(DomainException $e){$error=$e->getMessage();} catch(Throwable $e){$error='Der Vereinsbereich ist noch nicht eingerichtet oder momentan nicht verfügbar.';error_log('THV portal: '.$e->getMessage());}
$flash=$_SESSION['flash']??'';unset($_SESSION['flash']);
$tab=(string)($_GET['tab']??'start');$editor=thv_role($user,'editor','admin');$admin=thv_role($user,'admin');
if(!in_array($tab,['start','redaktion','mitglieder','anfragen','konto'],true)||($tab==='redaktion'&&!$editor)||($tab==='mitglieder'&&!$admin))$tab='start';
thv_layout_start('Vereinsbereich');
?>
<div class="club-heading"><div><p class="club-kicker">Traiskirchner Hundeverein</p><h1>Vereinsbereich</h1><p>Gemeinsam organisieren. Einfach informiert bleiben.</p></div><?php if($user):?><form method="post"><?php thv_action('logout');?><button class="secondary" type="submit">Abmelden</button></form><?php endif;?></div>
<?php if($error):?><p class="club-message error" role="alert"><?=h($error)?></p><?php endif;?>
<?php if($flash):?><p class="club-message" role="status"><?=h($flash)?></p><?php endif;?>
<?php if(!$secure):?><section class="club-card"><h2>Geschützter Zugang</h2><p>Bitte öffne die Website über HTTPS, um dich anzumelden.</p></section>
<?php elseif(!$ready):?><section class="club-card"><h2>Der interne Bereich wird eingerichtet</h2><p>Der Zugang steht nach der Freischaltung durch den Verein zur Verfügung.</p><a class="secondary" href="kontakt.html">Kontakt zum Verein</a></section>
<?php elseif(!$state['users']):?>
<section class="club-card narrow"><h2>Ersteinrichtung</h2><p>Nur für die zuständige Vereinsverwaltung. Ein privater Einrichtungsschlüssel ist erforderlich.</p>
<form method="post" class="club-form"><?php thv_action('setup'); thv_field('Einrichtungsschlüssel','key','password');thv_field('Name','name');thv_field('E-Mail-Adresse','email','email');thv_field('Eigenes Passwort (mindestens 12 Zeichen)','password','password');?><button>Einrichten</button></form></section>
<?php elseif(!$user):?>
<section class="club-card narrow"><h2>Willkommen zurück</h2><p>Melde dich mit deinem Vereinszugang an. Den Zugang erhältst du von der Vereinsverwaltung.</p>
<form method="post" class="club-form"><?php thv_action('login');thv_field('E-Mail-Adresse','email','email');?><label>Passwort<input name="password" type="password" autocomplete="current-password" required></label><button>Anmelden</button></form><p class="small">Zugang vergessen? <a href="kontakt.html">Vereinsverwaltung kontaktieren</a>.</p></section>
<?php elseif($user['change']):?>
<section class="club-card narrow"><h2>Dein eigenes Passwort</h2><p>Bitte ersetze das Startpasswort durch ein persönliches Passwort mit mindestens 12 Zeichen.</p><form method="post" class="club-form"><?php thv_action('password');thv_field('Startpasswort','old','password');thv_field('Neues Passwort','password','password');?><button>Passwort speichern</button></form></section>
<?php else:?>
<nav class="club-tabs" aria-label="Vereinsbereich"><?php foreach(['start'=>'Übersicht','anfragen'=>'Kursanfragen','redaktion'=>'Redaktion','mitglieder'=>'Mitglieder','konto'=>'Mein Konto'] as $key=>$name):if(($key==='redaktion'&&!$editor)||($key==='mitglieder'&&!$admin))continue;?><a href="?tab=<?=h($key)?>" <?=$tab===$key?'aria-current="page"':''?>><?=h($name)?></a><?php endforeach;?></nav>
<?php if($tab==='start'):?>
<section class="club-card"><h2>Hallo, <?=h($user['name'])?>!</h2><p>Hier findest du neue Informationen, veröffentlichte Kursangebote und deine Anfragen.</p><div class="club-shortcuts"><a href="?tab=anfragen">Meine Kursanfragen <span>→</span></a><?php if($editor):?><a href="?tab=redaktion">Beitrag oder Kurs erstellen <span>→</span></a><a href="website-studio.php">Website Studio <span>→</span></a><?php endif;?><?php if($admin):?><a href="?tab=mitglieder">Mitglieder verwalten <span>→</span></a><?php endif;?></div></section>
<section class="club-card"><h2>Vereinsnachrichten</h2><?php $any=false;foreach(array_reverse($state['entries']) as $e):if($e['type']!=='post'||!thv_visible($e,$user))continue;$any=true;?><article class="club-entry"><span class="tag"><?= $e['visibility']==='members'?'Nur für Mitglieder':'Öffentlich' ?></span><h3><?=h($e['title'])?></h3><p><?=h($e['summary'])?></p><details><summary>Weiterlesen</summary><p class="long-text"><?=h($e['body'])?></p></details></article><?php endforeach;if(!$any):?><p class="small">Noch keine neuen Beiträge.</p><?php endif;?></section>
<section class="club-card"><h2>Kursangebote</h2><?php $any=false;foreach(array_reverse($state['entries']) as $e):if($e['type']!=='course'||!thv_visible($e,$user))continue;$any=true;?><article class="club-entry"><h3><?=h($e['title'])?></h3><p><?=h($e['summary'])?></p><?php if($e['schedule']):?><p class="small"><?=h($e['schedule'])?></p><?php endif;?><details><summary>Details und Teilnahme anfragen</summary><p class="long-text"><?=h($e['body'])?></p><form method="post" class="club-form"><?php thv_action('request');?><input type="hidden" name="id" value="<?=h($e['id'])?>"><?php thv_area('Anmerkung (optional, keine sensiblen Daten)','note','',false,2);?><button>Teilnahme anfragen</button></form></details></article><?php endforeach;if(!$any):?><p class="small">Neue Angebote erscheinen hier, sobald sie freigegeben sind.</p><?php endif;?><a class="secondary" href="training.html">Alle regulären Kurse ansehen</a></section>
<?php elseif($tab==='redaktion'):?>
<?php $edit=$state['entries'][(string)($_GET['edit']??'')]??null;$type=$edit['type']??((($_GET['neu']??'')==='course')?'course':'post');$data=$edit??[];?>
<section class="club-card"><div class="section-line"><h2>Redaktion</h2><div class="club-actions"><a class="secondary" href="?tab=redaktion&amp;neu=post">+ Beitrag</a><a class="secondary" href="?tab=redaktion&amp;neu=course">+ Kurs</a></div></div><p>Entwürfe vorbereiten, Beiträge veröffentlichen oder zusätzliche Kurse anlegen.</p><a class="secondary" href="website-studio.php">Website Studio öffnen</a>
<?php if($edit||isset($_GET['neu'])):?><form method="post" class="club-form editor-form"><?php thv_action('save-entry');?><input type="hidden" name="id" value="<?=h($data['id']??'')?>"><input type="hidden" name="type" value="<?=h($type)?>"><h3><?= $edit?'Eintrag bearbeiten':($type==='course'?'Neuer Kurs':'Neuer Beitrag') ?></h3><?php thv_field('Titel','title','text',$data['title']??'');thv_area('Kurze Zusammenfassung','summary',$data['summary']??'',true,2);thv_area('Ausführlicher Inhalt','body',$data['body']??'',true,8);thv_field('Termin / Datum (optional)','date','date',$data['date']??'',false);if($type==='course'){thv_field('Alter / Voraussetzungen (optional)','age','text',$data['age']??'',false);thv_field('Kurszeit / Ort (optional)','schedule','text',$data['schedule']??'',false);}thv_select('Titelbild','image',thv_images(),$data['image']??'');thv_select('Sichtbarkeit','visibility',['draft'=>'Entwurf (nicht veröffentlicht)','public'=>'Öffentlich','members'=>'Nur Mitglieder'],$data['visibility']??'draft');?><div class="club-actions"><button>Speichern</button><a class="secondary" href="?tab=redaktion">Abbrechen</a></div></form><?php endif;?>
<div class="club-list"><?php if(!$state['entries']):?><p class="small">Noch keine redaktionellen Einträge. Erstelle deinen ersten Beitrag oder Kurs.</p><?php endif;foreach(array_reverse($state['entries']) as $e):?><article class="club-entry"><span class="tag"><?=h(['draft'=>'Entwurf','public'=>'Öffentlich','members'=>'Nur Mitglieder'][$e['visibility']])?> · <?= $e['type']==='course'?'Kurs':'Beitrag' ?></span><h3><?=h($e['title'])?></h3><p><?=h($e['summary'])?></p><div class="club-actions"><a class="secondary" href="?tab=redaktion&amp;edit=<?=h($e['id'])?>">Bearbeiten</a><?php if($e['visibility']==='public'):?><a class="secondary" href="vereinsartikel.php?id=<?=h($e['id'])?>">Ansehen</a><?php endif;if($e['visibility']!=='draft'):?><form method="post"><?php thv_action('archive');?><input type="hidden" name="id" value="<?=h($e['id'])?>"><button class="secondary" type="submit">Als Entwurf</button></form><?php endif;?></div></article><?php endforeach;?></div></section>
<?php elseif($tab==='mitglieder'):?>
<section class="club-card"><h2>Mitglieder verwalten</h2><p>Nur die Vereinsverwaltung kann Zugänge anlegen, Rollen zuweisen und sperren. Startpasswörter werden einmalig angezeigt.</p><details><summary>Neuen Zugang anlegen</summary><form method="post" class="club-form"><?php thv_action('create-user');thv_field('Name','name');thv_field('E-Mail-Adresse','email','email');thv_select('Rolle','role',['member'=>'Mitglied','editor'=>'Redaktion','admin'=>'Vereinsverwaltung'],'member');?><button>Zugang erstellen</button></form></details>
<?php foreach($state['users'] as $u):?><article class="club-entry"><span class="tag"><?=h(['member'=>'Mitglied','editor'=>'Redaktion','admin'=>'Vereinsverwaltung'][$u['role']])?> · <?=$u['active']?'Aktiv':'Gesperrt'?></span><h3><?=h($u['name'])?></h3><p><?=h($u['email'])?></p><?php if($u['id']!==$user['id']):?><form method="post" class="club-form inline"><?php thv_action('user-update');?><input type="hidden" name="id" value="<?=h($u['id'])?>"><?php thv_select('Rolle','role',['member'=>'Mitglied','editor'=>'Redaktion','admin'=>'Vereinsverwaltung'],$u['role']);?><div class="club-actions"><button name="mode" value="role" class="secondary">Rolle speichern</button><button name="mode" value="toggle" class="secondary"><?=$u['active']?'Sperren':'Aktivieren'?></button><button name="mode" value="reset" class="secondary">Passwort zurücksetzen</button></div></form><?php endif;?></article><?php endforeach;?></section>
<?php elseif($tab==='anfragen'):?>
<section class="club-card"><h2><?=$editor?'Kursanfragen verwalten':'Meine Kursanfragen'?></h2><p>Eine Anfrage ist noch keine bestätigte Buchung.</p><?php $any=false;$labels=['pending'=>'Offen','confirmed'=>'Bestätigt','waitlist'=>'Warteliste','cancelled'=>'Storniert'];foreach(array_reverse($state['requests']) as $q):if(!$editor&&$q['user']!==$user['id'])continue;$any=true;$e=$state['entries'][$q['course']]??null;$u=$state['users'][$q['user']]??null;?><article class="club-entry"><span class="tag"><?=h($labels[$q['status']])?></span><h3><?=h($e['title']??'Kurs')?></h3><?php if($editor&&$u):?><p><?=h($u['name'])?> · <?=h($u['email'])?></p><?php endif;if($q['note']):?><p class="long-text"><?=h($q['note'])?></p><?php endif;?><p class="small"><?=h(substr($q['created'],0,10))?></p><?php if($editor):?><form method="post" class="club-form inline"><?php thv_action('request-status');?><input type="hidden" name="id" value="<?=h($q['id'])?>"><?php thv_select('Status','status',$labels,$q['status']);?><button class="secondary">Speichern</button></form><?php elseif($q['status']!=='cancelled'):?><form method="post"><?php thv_action('request-status');?><input type="hidden" name="id" value="<?=h($q['id'])?>"><input type="hidden" name="status" value="cancelled"><button class="secondary">Anfrage zurückziehen</button></form><?php endif;?></article><?php endforeach;if(!$any):?><p class="small">Noch keine Anfragen vorhanden.</p><?php endif;?></section>
<?php else:?>
<section class="club-card narrow"><h2>Mein Konto</h2><p><?=h($user['name'])?> · <?=h($user['email'])?></p><form method="post" class="club-form"><?php thv_action('password');thv_field('Bisheriges Passwort','old','password');thv_field('Neues Passwort (mindestens 12 Zeichen)','password','password');?><button>Passwort ändern</button></form></section>
<?php endif;endif;thv_layout_end();?>
