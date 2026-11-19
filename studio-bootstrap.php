<?php
declare(strict_types=1);
$config=__DIR__.'/studio-site.php';
function studio_config(): array {static $cfg=null;return $cfg??=is_file(__DIR__.'/studio-site.php')?(require __DIR__.'/studio-site.php'):['adapter'=>'club','name'=>'Traiskirchner Hundeverein'];}
function studio_name(): string{return (string)(studio_config()['name']??'Meine Website');}
function studio_home(): string{return (studio_config()['adapter']??'club')==='standalone'?'studio-login.php':'vereinsbereich.php';}
require_once __DIR__.((studio_config()['adapter']??'club')==='standalone'?'/studio-standalone-core.php':'/verein-core.php');
