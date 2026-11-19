<?php
declare(strict_types=1);
// A site-specific, revocable sign-in token. Passwords never leave password_hash().
function thv_keep_name():string{return 'STUDIO_KEEP_'.substr(hash('sha256',realpath(__DIR__)),0,14);}
function thv_keep_path():string{$p=str_replace('\\','/',dirname((string)($_SERVER['SCRIPT_NAME']??'/')));return $p==='.'||$p==='/'?'/':rtrim($p,'/').'/';}
function thv_keep_cookie(string $v,int $exp):void{setcookie(thv_keep_name(),$v,['expires'=>$exp,'path'=>thv_keep_path(),'secure'=>true,'httponly'=>true,'samesite'=>'Lax']);if($v==='')unset($_COOKIE[thv_keep_name()]);else $_COOKIE[thv_keep_name()]=$v;}
function thv_keep_issue(array $u):void{
 if(empty($u['id'])||empty($u['change'])===false||empty($u['active']))return;
 $id=bin2hex(random_bytes(12));$secret=bin2hex(random_bytes(32));$exp=time()+30*86400;
 thv_state(function(&$s)use($id,$secret,$exp,$u){$s['remember']??=[];foreach($s['remember'] as $k=>$t)if(($t['expires']??0)<time())unset($s['remember'][$k]);$s['remember'][$id]=['user'=>$u['id'],'version'=>$u['version'],'hash'=>hash('sha256',$secret),'expires'=>$exp];if(count($s['remember'])>300){uasort($s['remember'],fn($a,$b)=>$a['expires']<=>$b['expires']);$s['remember']=array_slice($s['remember'],-250,null,true);}},true);
 thv_keep_cookie($id.'.'.$secret,$exp);
}
function thv_keep_revoke():void{
 $raw=$_COOKIE[thv_keep_name()]??'';if(is_string($raw)&&preg_match('/^([a-f0-9]{24})\\.[a-f0-9]{64}$/D',$raw,$m)){try{thv_state(function(&$s)use($m){unset($s['remember'][$m[1]]);},true);}catch(Throwable $e){}}
 thv_keep_cookie('',time()-3600);
}
function thv_keep_resume():void{
 if(isset($_SESSION['uid'])||!thv_secure())return;
 $raw=$_COOKIE[thv_keep_name()]??'';
 if(!is_string($raw)||!preg_match('/^([a-f0-9]{24})\\.([a-f0-9]{64})$/D',$raw,$m))return;
 try{$u=thv_state(function($s)use($m){$t=$s['remember'][$m[1]]??null;if(!$t||($t['expires']??0)<=time()||!hash_equals((string)($t['hash']??''),hash('sha256',$m[2])))return null;$u=$s['users'][$t['user']??'']??null;return $u&&$u['active']&&!$u['change']&&($t['version']??0)===($u['version']??-1)?$u:null;});if($u){thv_login($u);return;}}catch(Throwable $e){}
 thv_keep_cookie('',time()-3600);
}
