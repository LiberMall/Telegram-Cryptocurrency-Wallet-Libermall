<?php
$hostName = 'localhost';
$userName = 'tegromoneybot_bot';
$password = 'TV0Up5ARw036c';
$databaseName = 'tegromoneybot_bot';

define('TMP_DIR', __DIR__ . '/../tmp');
if (!is_dir(TMP_DIR)) {
    mkdir(TMP_DIR, 0700, true);
} else {
    @chmod(TMP_DIR, 0700);
}
?>
