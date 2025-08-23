<?php
$translations = [];
$user_lang = 'en';

function loadTranslations(){
    global $translations;
    $base = __DIR__ . '/lang';
    foreach(glob($base.'/*.php') as $file){
        $lang = basename($file, '.php');
        $translations[$lang] = include $file;
    }
}

function getUserLang($chat_id){
    global $link;
    $res = mysqli_query($link, "SELECT `lang` FROM `users` WHERE `chatid`='".mysqli_real_escape_string($link,$chat_id)."'");
    if($row = mysqli_fetch_assoc($res)){
        return $row['lang'] ?: 'en';
    }
    return 'en';
}

function setUserLang($chat_id, $lang){
    global $link;
    $lang = mysqli_real_escape_string($link, $lang);
    mysqli_query($link, "UPDATE `users` SET `lang`='$lang' WHERE `chatid`='".mysqli_real_escape_string($link,$chat_id)."'");
}

function t($key){
    global $translations, $user_lang;
    return $translations[$user_lang][$key] ?? $key;
}

function languageMenu(){
    global $chat_id;
    $arInfo["inline_keyboard"][0][0]["text"] = 'English';
    $arInfo["inline_keyboard"][0][0]["callback_data"] = 'setlang_en';
    $arInfo["inline_keyboard"][0][1]["text"] = 'Русский';
    $arInfo["inline_keyboard"][0][1]["callback_data"] = 'setlang_ru';
    send($chat_id, t('choose_language'), $arInfo);
}
