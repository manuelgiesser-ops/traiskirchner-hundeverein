<?php
declare(strict_types=1);
ini_set('display_errors','0');
date_default_timezone_set('Europe/Vienna');
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function thv_private(): string {
    $root=realpath(__DIR__); $dir=dirname($root).'/thv-private';
    if (!is_dir($dir) || is_link($dir) || !is_writable($dir)) throw new RuntimeException('Vereinsbereich noch nicht eingerichtet.');
    $real=realpath($dir); $web=realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__) ?: $root;
    if ($real===false || $real===$web || str_starts_with($real,$web.DIRECTORY_SEPARATOR)) throw new RuntimeException('Privater Speicher nicht verfügbar.');
    return $real;
}
function thv_empty(): array { return ['users'=>[], 'entries'=>[], 'requests'=>[], 'attempts'=>[]]; }
function thv_state(callable $callback, bool $write=false) {
    $dir=thv_private(); $path=$dir.'/vereinsdaten.json'; $lock=fopen($dir.'/vereinsdaten.lock','c');
    if (!$lock) throw new RuntimeException('Speicher nicht verfügbar.');
    try {
        if (!flock($lock,$write?LOCK_EX:LOCK_SH)) throw new RuntimeException('Speicher nicht verfügbar.');
        $state=is_file($path)?json_decode((string)file_get_contents($path),true,512,JSON_THROW_ON_ERROR):thv_empty();
        if (!is_array($state)||!isset($state['users'],$state['entries'],$state['requests'],$state['attempts'])) throw new RuntimeException('Speicher nicht verfügbar.');
        $result=$callback($state);
        if ($write) {
            $tmp=tempnam($dir,'thv-'); if (!$tmp) throw new RuntimeException('Speichern fehlgeschlagen.');
            try { chmod($tmp,0600); $json=json_encode($state,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
                if (file_put_contents($tmp,$json)!==strlen($json)||!rename($tmp,$path)) throw new RuntimeException('Speichern fehlgeschlagen.');
            } finally { if(is_file($tmp)) unlink($tmp); }
        }
        return $result;
    } finally { flock($lock,LOCK_UN); fclose($lock); }
}
function thv_session(): void {
    ini_set('session.use_strict_mode','1'); ini_set('session.use_only_cookies','1');
    session_name('THV_VEREIN'); session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
    if(isset($_SESSION['uid']) && (time()-($_SESSION['last']??0)>1800 || time()-($_SESSION['start']??0)>43200)) { $_SESSION=[]; session_regenerate_id(true); }
    $_SESSION['last']=time(); $_SESSION['csrf']??=bin2hex(random_bytes(32)); thv_keep_resume();
}
function thv_headers(bool $private=true): void {
    header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: same-origin'); header('X-Frame-Options: DENY');
    if($private){header('Cache-Control: no-store, private');header('X-Robots-Tag: noindex, nofollow');}
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'self'");
}
function thv_secure(): bool { return !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS'])!=='off'; }
function thv_user(array $s): ?array {
    $uid=$_SESSION['uid']??''; $u=$s['users'][$uid]??null;
    return $u && $u['active'] && ($u['version']??1)===($_SESSION['version']??0) ? $u : null;
}
function thv_role(?array $u,string ...$roles): bool { return $u!==null && !$u['change'] && in_array($u['role'],$roles,true); }
function thv_need(?array $u,string ...$roles): void { if(!thv_role($u,...$roles))throw new DomainException('Dafür fehlt die Berechtigung.'); }
function thv_csrf(): string { return '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">'; }
function thv_check(): void { if(!hash_equals($_SESSION['csrf']??'',(string)($_POST['csrf']??'') ) || empty($_SESSION['csrf'])) throw new DomainException('Bitte lade die Seite neu.'); }
function thv_text(string $key,int $max,bool $required=true): string {
    $v=trim((string)($_POST[$key]??''));
    if(($required&&$v==='')||strlen($v)>$max||!preg_match('//u',$v))throw new DomainException('Bitte prüfe deine Eingaben.');
    return $v;
}
function thv_email(): string { $v=strtolower(thv_text('email',254)); if(!filter_var($v,FILTER_VALIDATE_EMAIL))throw new DomainException('Bitte gib eine gültige E-Mail-Adresse ein.');return $v; }
function thv_password(string $key='password'): string { $p=(string)($_POST[$key]??''); if(strlen($p)<12||strlen($p)>72)throw new DomainException('Das Passwort muss 12 bis 72 Bytes lang sein.');return $p; }
function thv_id(): string { return bin2hex(random_bytes(12)); }
require_once __DIR__.'/studio-media-lib.php';
function thv_images(): array { $base=[''=>'Ohne Bild','assets/team-960.webp'=>'Vereinsteam','assets/jump-960.webp'=>'Agility','assets/puppy-960.webp'=>'Welpen','assets/dog-960.webp'=>'Grundkurs','assets/obed-960.webp'=>'Unterordnung','assets/hoopers-960.webp'=>'Hoopers','assets/walk-960.webp'=>'Gemeinsam unterwegs','assets/field-960.webp'=>'Trainingsgelände','assets/house-960.webp'=>'Vereinshaus']; try{return $base+thv_state(fn($s)=>thv_media_choices($s));}catch(Throwable $e){return $base;} }
require_once __DIR__.'/studio-remember.php';
function thv_new_user(string $email,string $name,string $password,string $role,bool $change): array {
    return ['id'=>thv_id(),'email'=>$email,'name'=>$name,'hash'=>password_hash($password,PASSWORD_DEFAULT),'role'=>$role,'active'=>true,'change'=>$change,'version'=>1,'created'=>date(DATE_ATOM)];
}
function thv_email_used(array $s,string $email): bool { foreach($s['users'] as $u)if($u['email']===$email)return true;return false; }
function thv_rate(array &$s,string $key,int $limit=8): bool {
    $now=time(); foreach($s['attempts'] as $k=>$a)if($now-($a['at']??0)>900)unset($s['attempts'][$k]);
    if(count($s['attempts'])>2048)$s['attempts']=array_slice($s['attempts'],-1024,null,true);
    $id=hash('sha256',$key); $a=$s['attempts'][$id]??['at'=>$now,'count'=>0];
    $a['count']++; $s['attempts'][$id]=$a; return $a['count']<=$limit;
}
function thv_login(array $u): void { session_regenerate_id(true); $_SESSION=['uid'=>$u['id'],'version'=>$u['version'],'start'=>time(),'last'=>time(),'csrf'=>bin2hex(random_bytes(32))]; }
function thv_redirect(string $tab=''): never { $dest=defined('THV_PORTAL')?(THV_PORTAL==='staff'?'verwaltung.php':'kursteilnehmer.php'):'vereinsbereich.php';header('Location: '.$dest.($tab?'?tab='.rawurlencode($tab):''),true,303);exit; }
function thv_flash(string $text): void { $_SESSION['flash']=$text; }
function thv_public(array $state): array {
    $out=[]; foreach($state['entries'] as $e)if($e['visibility']==='public')$out[]=array_intersect_key($e,array_flip(['id','type','title','summary','body','date','image','age','schedule','time','end','place','capacity','updated']));
    usort($out,fn($a,$b)=>strcmp($b['updated'],$a['updated'])); return $out;
}
function thv_entry_types(): array { return ['post'=>'Beitrag','course'=>'Kurs','event'=>'Veranstaltung','tournament'=>'Turnier']; }
function thv_entry_label(string $type): string { return thv_entry_types()[$type]??'Beitrag'; }
// Delete the editorial entry without losing existing inquiries.
function thv_entry_delete(array &$s,string $id): void {
    $e=$s['entries'][$id]??null;
    if(!$e)throw new DomainException('Eintrag nicht gefunden.');
    foreach($s['requests'] as &$q){
        if(($q['course']??'')===$id){
            $q['course_title']=$e['title'];
            $q['course_deleted']=true;
        }
    }unset($q);
    foreach($s['training_dates']??[] as $key=>$date){
        if(($date['course']??'')===$id)unset($s['training_dates'][$key]);
    }
    unset($s['entries'][$id]);
}
function thv_event_type(string $type): bool { return in_array($type,['event','tournament'],true); }
function thv_valid_time(string $time): bool { return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',$time)===1; }
function thv_visible(array $e,?array $u): bool { return $e['visibility']==='public'||($e['visibility']==='members'&&thv_role($u,'member','editor','admin')); }
function thv_layout_start(string $title,bool $portal=true): void { ?><!doctype html><html lang="de-AT"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="<?= $portal?'noindex,nofollow':'index,follow' ?>"><title><?=h($title)?> | Traiskirchner Hundeverein</title><link rel="icon" href="favicon-v24.svg" type="image/svg+xml"><link rel="stylesheet" href="vereinsbereich.css?v=3"></head><body><header class="club-header"><a href="index.html" class="club-brand"><img src="generated-media/thv-clubmark-v23.svg" alt="" width="48" height="48"><span>Traiskirchner<br>Hundeverein</span></a><a class="club-back" href="index.html">Zur Website</a></header><main class="club-main"><?php }
function thv_layout_end(): void { ?><footer class="club-footer"><a href="index.html">Startseite</a><a href="impressum.html">Impressum &amp; Datenschutz</a><a href="kontakt.html">Kontakt</a></footer></main></body></html><?php }
function thv_field(string $label,string $name,string $type='text',string $value='',bool $required=true): void { ?><label><?=h($label)?><input name="<?=h($name)?>" type="<?=h($type)?>" value="<?=h($value)?>" <?= $required?'required':'' ?> <?= $type==='password'?'autocomplete="new-password"':'' ?>></label><?php }
function thv_select(string $label,string $name,array $choices,string $value): void { ?><label><?=h($label)?><select name="<?=h($name)?>"><?php foreach($choices as $k=>$v):?><option value="<?=h($k)?>" <?=$k===$value?'selected':''?>><?=h($v)?></option><?php endforeach;?></select></label><?php }
function thv_area(string $label,string $name,string $value='',bool $required=true,int $rows=4): void { ?><label><?=h($label)?><textarea name="<?=h($name)?>" rows="<?=$rows?>" <?=$required?'required':''?>><?=h($value)?></textarea></label><?php }
function thv_action(string $action): void { echo thv_csrf().'<input type="hidden" name="action" value="'.h($action).'">'; }
