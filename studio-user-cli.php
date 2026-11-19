<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/studio-bootstrap.php';
if((studio_config()['adapter']??'club')!=='standalone')exit("Standalone installation required.\n");
if($argc!==4||!filter_var($argv[1],FILTER_VALIDATE_EMAIL)||!in_array($argv[3],['admin','editor'],true))exit("Usage: php studio-user-cli.php email name admin|editor\nPassword via STDIN (12–72 bytes).\n");
$email=strtolower($argv[1]);$name=trim($argv[2]);$role=$argv[3];
$password=rtrim((string)fgets(STDIN),"\r\n");
if(strlen($password)<12||strlen($password)>72||$name===''||strlen($name)>120)exit("Invalid name or password.\n");
thv_state(function(&$s)use($email,$name,$role,$password){foreach($s['users'] as $id=>$u)if($u['email']===$email){$s['users'][$id]['hash']=password_hash($password,PASSWORD_DEFAULT);$s['users'][$id]['role']=$role;$s['users'][$id]['name']=$name;$s['users'][$id]['active']=true;$s['users'][$id]['change']=false;$s['users'][$id]['version']++;return;} $u=thv_new_user($email,$name,$password,$role,false);$s['users'][$u['id']]=$u;},true);
echo "Benutzer gespeichert.\n";
