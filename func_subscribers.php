<?php
function registerSubscriber($chat_id, array $data){
    $file = __DIR__.'/subscribers.json';
    $first = isset($data['message']['from']['first_name']) ? $data['message']['from']['first_name'] : '';
    $last = isset($data['message']['from']['last_name']) ? $data['message']['from']['last_name'] : '';
    $usern = isset($data['message']['from']['username']) ? $data['message']['from']['username'] : '';
    $record = [
        'chat_id' => (int)$chat_id,
        'name' => trim(filter_var($first.' '.$last, FILTER_SANITIZE_FULL_SPECIAL_CHARS)),
        'username' => trim(filter_var($usern, FILTER_SANITIZE_FULL_SPECIAL_CHARS))
    ];
    $list = [];
    if(file_exists($file)){
        $content = file_get_contents($file);
        $list = json_decode($content, true);
        if(!is_array($list)) $list = [];
    }
    foreach($list as $item){
        if($item['chat_id'] === $record['chat_id']){
            return;
        }
    }
    $list[] = $record;
    file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    @chmod($file, 0600);
}
?>
