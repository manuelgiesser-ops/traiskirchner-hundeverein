<?php
declare(strict_types=1);
require __DIR__.'/studio-bootstrap.php';
if((studio_config()['adapter']??'club')!=='standalone'){header('Location: '.studio_home(),true,303);exit;}
thv_headers();thv_session();$error='';
try{
 if(!thv_secure())throw new DomainException('Bitte öffne den Editor über HTTPS.');
 if($_SERVER['REQUEST_METHOD']==='POST'){
  thv_check();
  if(($_POST['action']??'')==='logout'){thv_keep_revoke();$_SESSION=[];session_regenerate_id(true);header('Location: studio-login.php',true,303);exit;}
  $email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');
  if(strlen($email)>254||strlen($password)>256)throw new DomainException('Anmeldung nicht möglich.');
  $result=thv_state(function(&$s)use($email,$password){
   if(!thv_rate($s,'studio-login:'.($_SERVER['REMOTE_ADDR']??''),12))return null;
   foreach($s['users'] as $u)if($u['email']===$email&&thv_role($u,'admin','editor')&&password_verify($password,$u['hash']))return $u;
   return null;
  },true);
  if(!$result)throw new DomainException('Anmeldung nicht möglich. Bitte prüfe deine Daten oder versuche es später erneut.');
  thv_login($result);if(($_POST['remember']??'')==='1')thv_keep_issue($result);header('Location: website-studio.php',true,303);exit;
 }
 $u=thv_user(thv_state(fn($s)=>$s));
}catch(DomainException $e){$error=$e->getMessage();$u=null;}catch(Throwable $e){$error='Der Editor ist noch nicht eingerichtet.';$u=null;}
?><!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Website Studio – Anmeldung</title><link rel="stylesheet" href="studio-interface.css?v=52"></head><body class="studio-login"><main class="studio-login-card"><span class="studio-brand-mark" aria-hidden="true">S</span><h1>Website Studio</h1><p><?=h(studio_name())?></p><?php if($error):?><p role="alert"><?=h($error)?></p><?php endif;?><?php if(thv_role($u,'admin','editor')):?><a class="secondary" href="website-studio.php">Editor öffnen</a><form method="post"><?=thv_csrf()?><input type="hidden" name="action" value="logout"><button>Abmelden</button></form><?php else:?><form method="post"><?=thv_csrf()?><label>E-Mail-Adresse<input type="email" name="email" autocomplete="username" required></label><label>Passwort<input type="password" name="password" autocomplete="current-password" required></label><label class="remember-field"><input type="checkbox" name="remember" value="1"><span>Angemeldet bleiben (30 Tage auf diesem Gerät)</span></label><button type="submit">Anmelden</button></form><?php endif;?><a href="index.html">Zur Website</a></main></body></html>
