<?php
declare(strict_types=1);
require __DIR__.'/verein-core.php';
thv_headers(false);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-cache');
try {$entries=thv_state(fn($s)=>thv_public($s));foreach($entries as &$e)unset($e['body']);unset($e);echo json_encode(['entries'=>$entries],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);}catch(Throwable $e){echo '{"entries":[]}';}
