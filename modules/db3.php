<?php
// yangiasr.uz [database]
$db3_host = 'localhost';
$db3_user = 'yangiasr_uz';
$db3_base = 'yangiasr_uz';
$db3_pass = 'LQuW0BdCOo9YQrPI';
$db3 = new my_db("mysql:host=$db3_host;dbname=$db3_base",$db3_user,$db3_pass,[
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
]);
?>