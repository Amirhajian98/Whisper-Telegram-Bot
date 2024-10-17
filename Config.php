<?php
///////AmirHajian
$Config = [
    'token'=> '',
    'admins'=> [
        123456789
    ],
    'channel_id'=> [
        '@'
    ], //Telegram Channel ID
    'channel_link'=> [
        'https://t.me/'
    ], //Telegram Channel Link
    'db_info'=> [
        'db_name'=> '',
        'db_user'=> '',
        'db_pass'=> ''
    ]
]; //DataBase
$domin = "";//دامین + پوشه
$sql = new mysqli('localhost',$Config['db_info']['db_user'],$Config['db_info']['db_pass'],$Config['db_info']['db_name']);
$sql->query("SET CHARACTER SET 'utf8mb4'");
$sql->set_charset('utf8mb4');


$sql->query("CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT(20) PRIMARY KEY,
    `command` VARCHAR(50) DEFAULT 'none',
    `lang` VARCHAR(50) DEFAULT 'fa',
    `rec` VARCHAR(500) DEFAULT '',
    `block` TINYINT(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;");

$sql->query("CREATE TABLE IF NOT EXISTS `najva` (
    `id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `owner` BIGINT(20) DEFAULT '0',
    `username|id` VARCHAR(100) DEFAULT '',
    `fozol` VARCHAR(300) DEFAULT '',
    `text` VARCHAR(300) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;");

$sql->query("CREATE TABLE IF NOT EXISTS `panel` (
    `id` INT(2) PRIMARY KEY,
    `power` TINYINT(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;");

if ($sql->query("SELECT `id` FROM `panel`")->num_rows<1) {
    $sql->query("INSERT INTO `panel` (`id`) VALUES ('85');");
}

$sql->query("CREATE TABLE IF NOT EXISTS `sendAll` (
    `id` INT(2) PRIMARY KEY,
    `type` VARCHAR(100) DEFAULT '-',
    `count` INT(10) DEFAULT '0',
    `sendtype` VARCHAR(100) DEFAULT '-',
    `txtcap` VARCHAR(2000) DEFAULT '-',
    `media` VARCHAR(2000) DEFAULT '-',
    `value` INT(2) DEFAULT '0',
    `from_id` BIGINT(20) DEFAULT '0',
    `msg_id` BIGINT(30) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;");

if ($sql->query("SELECT `id` FROM `sendAll`")->num_rows<1) {
    $sql->query("INSERT INTO `sendAll` (`id`) VALUES ('85');");
}