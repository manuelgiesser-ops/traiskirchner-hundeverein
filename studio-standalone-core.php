<?php
declare(strict_types=1);
ini_set('display_errors','0');
date_default_timezone_set('Europe/Vienna');
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function thv_private(): string {
    $dir=(string)(studio_config()['storage']??'');
    $real=realpath($dir);$web=realpath($_SERVER['DOCUMENT_ROOT']??__DIR__)?:realpath(__DIR__);
    if(!$real||!is_dir($real)||!is_writable($real)||is_link($dir)||$real===$web||str_starts_with($real,$web.DIRECTORY_SEPARATOR))throw new RuntimeException('Privater Speicher fehlt oder liegt im Webroot.');
    $marker=$real.'/studio-site.id';$id=hash('sha256',realpath(__DIR__));
    if(!is_file($marker)||!hash_equals($id,trim((string)file_get_contents($marker))))throw new RuntimeException('Speicher dieser Website ist nicht eingerichtet.');
    return $real;
}
function thv_empty(): array { return ['users'=>[], 'entries'=>[], 'requests'=>[], 'attempts'=>[]]; }
function thv_state(callable $callback, bool $write=false) {
    $dir=thv_private(); $path=$dir.'/studio-data.json'; $lock=fopen($dir.'/studio-data.lock','c');
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
    session_name('STUDIO_'.substr(hash('sha256',realpath(__DIR__)),0,20)); session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
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
