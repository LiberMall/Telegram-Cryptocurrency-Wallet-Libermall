<?php

require_once __DIR__ . '/../func_gen.php';

define('TOKEN', 'TEST_TOKEN');
$chat_id = 1;

$ok = true;
if(delMessage(null, null) !== false){
    echo "delMessage without params should return false\n";
    $ok = false;
}
if(delMessage2(5, null) !== false){
    echo "delMessage2 without cid should return false\n";
    $ok = false;
}
if(delMessage2(null, null) !== false){
    echo "delMessage2 without params should return false\n";
    $ok = false;
}

if($ok){
    echo "All tests passed\n";
    exit(0);
} else {
    exit(1);
}

